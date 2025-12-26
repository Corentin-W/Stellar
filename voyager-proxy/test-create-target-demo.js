// Script de test pour créer une target et afficher la requête complète
import { v4 as uuidv4 } from 'uuid';

const testCreateTarget = async () => {
  try {
    console.log('🎯 TEST CREATION DE TARGET\n');
    console.log('='.repeat(80));

    // 1. D'abord récupérer un Set existant
    console.log('\n📋 ÉTAPE 1: Récupération des Sets existants...\n');

    const setsResponse = await fetch('http://localhost:3003/api/robotarget/sets');
    const setsData = await setsResponse.json();

    if (!setsData.success || !setsData.sets || setsData.sets.length === 0) {
      console.log('❌ Aucun Set trouvé. Créez d\'abord un Set!');
      return;
    }

    const firstSet = setsData.sets[0];
    console.log(`✅ Set trouvé: ${firstSet.SetName} (${firstSet.GuidSet})`);

    // 2. Récupérer les séquences de base
    console.log('\n📋 ÉTAPE 2: Récupération des séquences de base...\n');

    const seqResponse = await fetch('http://localhost:3003/api/robotarget/base-sequences');
    const seqData = await seqResponse.json();

    if (!seqData.success || !seqData.sequences || seqData.sequences.length === 0) {
      console.log('❌ Aucune séquence de base trouvée!');
      return;
    }

    const firstSeq = seqData.sequences[0];
    console.log(`✅ Séquence trouvée: ${firstSeq.NameSeq} (${firstSeq.GuidBaseSequence})`);

    // 3. Préparer les paramètres de la target selon la documentation
    const targetUID = uuidv4();
    const targetGuid = uuidv4();

    const targetParams = {
      // Paramètres d'identification (obligatoires)
      GuidTarget: targetGuid,
      RefGuidSet: firstSet.GuidSet,
      RefGuidBaseSequence: firstSeq.GuidBaseSequence,
      TargetName: "M42 - Orion Nebula (TEST)",
      Tag: "Nebula",
      DateCreation: Math.floor(Date.now() / 1000), // Epoch timestamp

      // Coordonnées (obligatoires)
      RAJ2000: 5.588,      // RA en heures
      DECJ2000: -5.391,    // DEC en degrés
      PA: 0,               // Position Angle

      // Statut
      Status: 0,           // 0 = Activé
      StatusOp: 0,         // 0 = Idle
      Priority: 2,         // 0-4 (2 = Normal)
      Note: "Target créée par test API",

      // Overrides d'acquisition
      IsRepeat: false,
      Repeat: 1,
      IsFinishActualExposure: false,
      IsCoolSetPoint: false,
      CoolSetPoint: -10,
      IsWaitShot: false,
      WaitShot: 0,
      IsGuideTime: false,
      GuideTime: 2.0,
      IsOffsetRF: false,
      OffsetRF: 0,

      // Contraintes
      C_ID: uuidv4(),
      C_Mask: "BK",        // B=Alt min, K=Moon Down
      C_AltMin: 30,
      C_SqmMin: 0,
      C_HAStart: -5,
      C_HAEnd: 5,
      C_MoonDown: true,
      C_MoonPhaseMin: 0,
      C_MoonPhaseMax: 100,
      C_MoonDistanceDegree: 30,
      C_MoonDistanceLorentzian: 0,
      C_HFDMeanLimit: 0,
      C_MaxTimeForDay: 0,
      C_AirMassMin: 1.0,
      C_AirMassMax: 2.5,

      // Paramètres objets dynamiques (OBLIGATOIRE selon doc)
      TType: 0,            // 0=DSO (OBLIGATOIRE!)
      TKey: "",
      TName: "",
      IsDynamicPointingOverride: false,
      DynamicPointingOverride: 0,
      DynEachX_Seconds: 0,
      DynEachX_Realign: false
    };

    // 4. Envoyer la requête via l'endpoint test-mac pour voir la requête complète
    console.log('\n📤 ÉTAPE 3: Envoi de la commande RemoteRoboTargetAddTarget...\n');

    const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        method: 'RemoteRoboTargetAddTarget',
        params: targetParams,
        macFormula: {
          sep1: '|| |',   // 1 espace (selon doc section 4)
          sep2: '||  |',  // 2 espaces
          sep3: '|| |'    // 1 espace
        }
      })
    });

    const result = await response.json();

    console.log('='.repeat(80));
    console.log('\n📦 REQUÊTE COMPLÈTE ENVOYÉE À VOYAGER:\n');
    console.log('='.repeat(80));
    console.log(JSON.stringify(result.command, null, 2));

    console.log('\n='.repeat(80));
    console.log('\n🔐 DÉTAILS DU MAC:\n');
    console.log('='.repeat(80));
    console.log(`Formula:      ${result.macInfo.formula}`);
    console.log(`MAC String:   ${result.macInfo.string}`);
    console.log(`MAC (Base64): ${result.macInfo.mac}`);

    console.log('\n='.repeat(80));
    console.log('\n📊 RÉSULTAT:\n');
    console.log('='.repeat(80));

    if (result.success) {
      console.log('✅ SUCCESS! Target créée avec succès!');
      console.log('\nRésultat complet:');
      console.log(JSON.stringify(result.result, null, 2));
    } else {
      console.log('❌ ÉCHEC ou TIMEOUT');
      console.log(`Erreur: ${result.error || 'Pas de réponse reçue'}`);
      if (result.result) {
        console.log('\nRésultat reçu:');
        console.log(JSON.stringify(result.result, null, 2));
      }
    }

    console.log('\n='.repeat(80));
    console.log('\n📋 COMPARAISON AVEC LA DOCUMENTATION:\n');
    console.log('='.repeat(80));
    console.log('✅ TType présent (OBLIGATOIRE selon doc ligne 88)');
    console.log('✅ MAC avec algorithme "1-2-1" (1 espace, 2 espaces, 1 espace)');
    console.log('✅ Tous les paramètres d\'identification présents');
    console.log('✅ Tous les paramètres de position présents');
    console.log('✅ Tous les paramètres de contraintes présents');

  } catch (error) {
    console.error('\n❌ ERREUR:', error.message);
    console.error(error.stack);
  }
};

testCreateTarget();
