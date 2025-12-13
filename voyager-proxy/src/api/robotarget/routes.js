import express from 'express';
import RoboTargetCommands from '../../voyager/robotarget/commands.js';
import { validateSet, validateTarget, validateShot } from './validators.js';

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
    try {
      const { guid_set, set_name } = req.body;

      const result = await roboTargetCommands.addSet({
        GuidSet: guid_set,
        SetName: set_name || `Set_${guid_set.substring(0, 8)}`,
      });

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
   * POST /api/robotarget/targets
   * Créer une nouvelle Target RoboTarget
   */
  router.post('/targets', validateTarget, async (req, res) => {
    try {
      const targetData = req.body;

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
