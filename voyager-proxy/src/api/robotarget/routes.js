import express from 'express';
import RoboTargetCommands from '../../voyager/robotarget/commands.js';
import { validateSet, validateTarget, validateShot } from './validators.js';
import { setupTestMacRoute } from './test-mac-route.js';

const router = express.Router();

/**
 * RoboTarget REST API Routes
 *
 * Ces routes permettent de gérer les cibles RoboTarget via le proxy Voyager.
 */

export default (voyagerConnection, io) => {
  const roboTargetCommands = new RoboTargetCommands(voyagerConnection);

  /**
   * POST /api/robotarget/sets
   * Créer un nouveau Set RoboTarget
   */
  router.post('/sets', validateSet, async (req, res) => {
    console.log('📥 [RoboTarget API] POST /api/robotarget/sets received');
    console.log('📦 [RoboTarget API] Request body:', JSON.stringify(req.body, null, 2));
    console.log('🔑 [RoboTarget API] Headers:', JSON.stringify(req.headers, null, 2));

    try {
      // Support both snake_case (from Laravel) and direct parameter names
      const guidSet = req.body.Guid || req.body.guid_set;
      const name = req.body.Name || req.body.set_name;
      const profileName = req.body.ProfileName || req.body.profile_name || 'Default.v2y';
      const isDefault = req.body.IsDefault !== undefined ? req.body.IsDefault : (req.body.is_default !== undefined ? req.body.is_default : false);
      const tag = req.body.Tag || req.body.tag || '';
      const status = req.body.Status !== undefined ? req.body.Status : (req.body.status !== undefined ? req.body.status : 0);
      const note = req.body.Note || req.body.note || '';

      // Use exact parameter names from Voyager API documentation
      // IMPORTANT: Match the exact format from official example (line 1010):
      // {"Guid":"...","Name":"Pippolo","ProfileName":"TestFlatNoMount.v2y","IsDefault":false,"Status":0,"Note":"","UID":"...","MAC":"..."}
      const setParams = {
        Guid: guidSet,
        Name: name || `Set_${guidSet.substring(0, 8)}`,
        ProfileName: profileName.endsWith('.v2y') ? profileName : `${profileName}.v2y`,
        IsDefault: Boolean(isDefault), // Keep as boolean (false/true), NOT integer
        Status: status,
        Note: note || '',  // Send empty string, not omit (as per official example)
      };

      console.log('🔍 DEBUG: Parameters being sent to addSet():', JSON.stringify(setParams, null, 2));

      const result = await roboTargetCommands.addSet(setParams);

      res.json({
        success: true,
        message: 'Set créé avec succès',
        result: result,
      });

    } catch (error) {
      console.error('Error creating set:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la création du set',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/sets
   * Lister tous les Sets RoboTarget
   */
  router.get('/sets', async (req, res) => {
    try {
      const result = await roboTargetCommands.listSets();

      // Extract the list from the result (ParamRet.list or parsed.params.list)
      const sets = result.ParamRet?.list || result.parsed?.params?.list || [];

      res.json({
        success: true,
        sets: sets.map(set => ({
          GuidSet: set.guid,
          SetName: set.setname,
          ProfileName: set.profilename,
          IsDefault: set.isdefault,
          Status: set.status,
          Tag: set.tag,
          Note: set.note
        })),
      });
    } catch (error) {
      console.error('Error listing sets:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des sets',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/targets
   * Lister les Targets (optionnel: filtrer par set)
   */
  router.get('/targets', async (req, res) => {
    try {
      const { setGuid } = req.query;

      const result = await roboTargetCommands.listTargetsForSet(setGuid || '');

      res.json({
        success: true,
        targets: result,
      });
    } catch (error) {
      console.error('Error listing targets:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des targets',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/base-sequences
   * Lister toutes les séquences de base (templates) disponibles dans Voyager
   */
  router.get('/base-sequences', async (req, res) => {
    console.log('📥 [RoboTarget API] GET /api/robotarget/base-sequences received');

    try {
      const result = await roboTargetCommands.listBaseSequences();

      console.log('📊 [RoboTarget API] Raw result from Voyager:', JSON.stringify(result, null, 2));

      // Extract the list from the result (ParamRet.list or parsed.params.list)
      const sequences = result.ParamRet?.list || result.parsed?.params?.list || [];

      console.log(`✅ [RoboTarget API] Found ${sequences.length} base sequences`);

      res.json({
        success: true,
        sequences: sequences.map(seq => ({
          GuidBaseSequence: seq.guid,
          NameSeq: seq.basesequencename,
          FileName: seq.filename,
          ProfileName: seq.profilename,
          IsDefault: seq.isdefault,
        })),
      });
    } catch (error) {
      console.error('❌ [RoboTarget API] Error listing base sequences:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des séquences de base',
        error: error.message,
      });
    }
  });

  /**
   * POST /api/robotarget/targets
   * Créer une nouvelle Target RoboTarget
   */
  router.post('/targets', validateTarget, async (req, res) => {
    console.log('📥 [RoboTarget API] POST /api/robotarget/targets received');
    console.log('🎯 [RoboTarget API] Request body:', JSON.stringify(req.body, null, 2));
    console.log('🔑 [RoboTarget API] Headers:', JSON.stringify(req.headers, null, 2));
    console.log('📊 [RoboTarget API] Body keys:', Object.keys(req.body));

    try {
      const targetData = req.body;
      console.log('🔄 [RoboTarget API] Processing target data...');

      // Générer C_Mask si non fourni
      if (!targetData.C_Mask) {
        targetData.C_Mask = generateConstraintMask(targetData);
      }

      const result = await roboTargetCommands.addTarget(targetData);

      // Ajouter les shots
      if (targetData.Shots && Array.isArray(targetData.Shots)) {
        for (const shot of targetData.Shots) {
          await roboTargetCommands.addShot({
            RefGuidTarget: targetData.GuidTarget,
            ...shot,
          });
        }
      }

      res.json({
        success: true,
        message: 'Target créée avec succès',
        result: result,
        shots_added: targetData.Shots?.length || 0,
      });

    } catch (error) {
      console.error('Error creating target:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la création de la target',
        error: error.message,
      });
    }
  });

  /**
   * POST /api/robotarget/shots
   * Ajouter un Shot à une Target existante
   */
  router.post('/shots', validateShot, async (req, res) => {
    try {
      const shotData = req.body;

      const result = await roboTargetCommands.addShot(shotData);

      res.json({
        success: true,
        message: 'Shot ajouté avec succès',
        result: result,
      });

    } catch (error) {
      console.error('Error adding shot:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de l\'ajout du shot',
        error: error.message,
      });
    }
  });

  /**
   * PUT /api/robotarget/targets/:guid/status
   * Modifier le statut d'une Target (active/inactive)
   */
  router.put('/targets/:guid/status', async (req, res) => {
    try {
      const { guid } = req.params;
      const { status } = req.body;

      if (!['active', 'inactive'].includes(status)) {
        return res.status(400).json({
          success: false,
          message: 'Status invalide. Doit être "active" ou "inactive"',
        });
      }

      const isActive = status === 'active';

      const result = await roboTargetCommands.setTargetStatus({
        GuidTarget: guid,
        TargetActive: isActive,
      });

      res.json({
        success: true,
        message: `Target ${isActive ? 'activée' : 'désactivée'} avec succès`,
        result: result,
      });

    } catch (error) {
      console.error('Error setting target status:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la modification du statut',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/sessions/:targetGuid/result
   * Récupérer les résultats de session pour une Target
   */
  router.get('/sessions/:targetGuid/result', async (req, res) => {
    try {
      const { targetGuid } = req.params;

      const result = await roboTargetCommands.getSessionListByTarget({
        GuidTarget: targetGuid,
      });

      res.json({
        success: true,
        sessions: result,
      });

    } catch (error) {
      console.error('Error getting session results:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des sessions',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/targets/:guid/progress
   * Récupérer la progression en temps réel d'une Target
   */
  router.get('/targets/:guid/progress', async (req, res) => {
    try {
      const { guid } = req.params;

      // Récupérer les données de progression depuis Voyager
      const controlData = voyagerConnection.getLastControlData();

      // Filtrer pour cette target spécifique
      const targetProgress = {
        guid: guid,
        sequence_name: controlData?.SEQNAME || null,
        sequence_progress: controlData?.SEQPROGRESS || 0,
        current_image: controlData?.SEQCURRENTIMAGE || 0,
        total_images: controlData?.SEQTOTALIMAGES || 0,
        current_filter: controlData?.FILTER || null,
        hfd: controlData?.HFD || null,
        is_running: controlData?.SEQNAME === guid,
      };

      res.json({
        success: true,
        progress: targetProgress,
      });

    } catch (error) {
      console.error('Error getting target progress:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération de la progression',
        error: error.message,
      });
    }
  });

  /**
   * DELETE /api/robotarget/targets/:guid
   * Supprimer une Target
   */
  router.delete('/targets/:guid', async (req, res) => {
    try {
      const { guid } = req.params;

      // D'abord désactiver la target
      await roboTargetCommands.setTargetStatus({
        GuidTarget: guid,
        TargetActive: false,
      });

      // TODO: Ajouter une commande pour supprimer complètement si Voyager le permet

      res.json({
        success: true,
        message: 'Target désactivée avec succès',
      });

    } catch (error) {
      console.error('Error deleting target:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la suppression de la target',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/sessions/:sessionGuid/shots
   * Récupérer la liste des shots complétés pour une session
   */
  router.get('/sessions/:sessionGuid/shots', async (req, res) => {
    try {
      const { sessionGuid } = req.params;

      const result = await roboTargetCommands.getShotDoneBySessionList(sessionGuid);

      res.json({
        success: true,
        shots: result.parsed?.list || { done: [], deleted: [] },
      });

    } catch (error) {
      console.error('Error getting session shots:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des shots',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/sets/:setGuid/shots
   * Récupérer la liste des shots complétés pour un set
   */
  router.get('/sets/:setGuid/shots', async (req, res) => {
    try {
      const { setGuid } = req.params;

      const result = await roboTargetCommands.getShotDoneBySetList(setGuid);

      res.json({
        success: true,
        shots: result.parsed?.list || { done: [], deleted: [] },
      });

    } catch (error) {
      console.error('Error getting set shots:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des shots',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/shots/:shotGuid/jpg
   * Télécharger l'image JPG d'un shot
   */
  router.get('/shots/:shotGuid/jpg', async (req, res) => {
    try {
      const { shotGuid } = req.params;

      const result = await roboTargetCommands.getShotJpg(shotGuid);

      if (!result.parsed?.Base64Data) {
        return res.status(404).json({
          success: false,
          message: 'Image non trouvée ou non disponible',
        });
      }

      // Convert base64 to buffer
      const imageBuffer = Buffer.from(result.parsed.Base64Data, 'base64');

      // Send as image
      res.set({
        'Content-Type': 'image/jpeg',
        'Content-Length': imageBuffer.length,
        'Content-Disposition': `attachment; filename="shot_${shotGuid}.jpg"`,
      });

      res.send(imageBuffer);

    } catch (error) {
      console.error('Error getting shot JPG:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération de l\'image',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/shots/:shotGuid/metadata
   * Récupérer uniquement les métadonnées d'un shot (sans l'image)
   */
  router.get('/shots/:shotGuid/metadata', async (req, res) => {
    try {
      const { shotGuid } = req.params;

      const result = await roboTargetCommands.getShotJpg(shotGuid);

      if (!result.parsed) {
        return res.status(404).json({
          success: false,
          message: 'Métadonnées non trouvées',
        });
      }

      // Return metadata without Base64Data to save bandwidth
      const metadata = {
        hfd: result.parsed.HFD,
        starIndex: result.parsed.StarIndex,
        pixelDimX: result.parsed.PixelDimX,
        pixelDimY: result.parsed.PixelDimY,
        min: result.parsed.Min,
        max: result.parsed.Max,
        mean: result.parsed.Mean,
      };

      res.json({
        success: true,
        metadata,
      });

    } catch (error) {
      console.error('Error getting shot metadata:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des métadonnées',
        error: error.message,
      });
    }
  });

  /**
   * GET /api/robotarget/shots/since/:timestamp
   * Récupérer les shots complétés depuis un timestamp
   */
  router.get('/shots/since/:timestamp', async (req, res) => {
    try {
      const { timestamp } = req.params;
      const { targetGuid, setGuid } = req.query;

      const result = await roboTargetCommands.getShotDoneSinceList(
        parseInt(timestamp),
        targetGuid || '',
        setGuid || ''
      );

      res.json({
        success: true,
        shots: result.parsed?.list || { done: [], deleted: [] },
      });

    } catch (error) {
      console.error('Error getting shots since timestamp:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de la récupération des shots',
        error: error.message,
      });
    }
  });

  /**
   * POST /api/robotarget/open-api
   * Open API endpoint for commands using MD5 MAC (simpler authentication)
   * Used for: RemoteOpenRoboTargetGetShotDoneList, etc.
   */
  router.post('/open-api', async (req, res) => {
    try {
      const { method, params } = req.body;

      if (!method || !params) {
        return res.status(400).json({
          success: false,
          message: 'Missing required fields: method, params',
        });
      }

      const crypto = await import('crypto');

      // Generate UID if not provided
      const UID = params.UID || crypto.randomUUID();

      // Calculate MAC for Open API (MD5 simple)
      // Formula: MD5(SharedSecret + UID + RefGuidTarget)
      const sharedSecret = voyagerConnection.config.auth.sharedSecret;
      const refGuidTarget = params.RefGuidTarget || '';
      const macString = sharedSecret + UID + refGuidTarget;
      const MAC = crypto.createHash('md5').update(macString).digest('hex');

      // Build the command parameters with MAC
      const commandParams = {
        ...params,
        UID,
        MAC
      };

      // Send the command using RoboTargetCommands.send()
      const result = await roboTargetCommands.send(method, commandParams);

      res.json({
        success: true,
        result: result,
        method: method
      });

    } catch (error) {
      console.error('Error executing Open API command:', error);
      res.status(500).json({
        success: false,
        message: 'Erreur lors de l\'exécution de la commande Open API',
        error: error.message,
      });
    }
  });

  /**
   * Test MAC route - allows testing different MAC formulas interactively
   */
  setupTestMacRoute(router, roboTargetCommands, voyagerConnection);

  return router;
}

/**
 * Helper: Generate Constraint Mask
 */
function generateConstraintMask(targetData) {
  let mask = '';

  // B = AltMin (toujours présent)
  mask += 'B';

  // K = MoonDown
  if (targetData.C_MoonDown) {
    mask += 'K';
  }

  // O = HFD Mean Limit
  if (targetData.C_HFDMeanLimit && targetData.C_HFDMeanLimit > 0) {
    mask += 'O';
  }

  return mask;
}
