// Script autonome qui se connecte directement à Voyager pour créer une target
import VoyagerConnection from './src/voyager/connection.js';
import Commands from './src/voyager/commands.js';
import Auth from './src/voyager/auth.js';
import { v4 as uuidv4 } from 'uuid';

const config = {
  host: 'localhost',
  port: 5950,
  auth: {
    username: 'admin',
    password: '6383',
    sharedSecret: 'Dherbomez',
    macKey: 'Dherbomez',
    macWord1: 'Eye',
    macWord2: 'Voyager',
    macWord3: '2.5',
    macWord4: 'BYE',
    licenseNumber: ''
  },
  heartbeat: {
    interval: 5000,
    timeout: 15000
  }
};

async function main() {
  console.log('🎯 CRÉATION DE TARGET AVEC CONNEXION DIRECTE À VOYAGER\n');
  console.log('='.repeat(80));

  const connection = new VoyagerConnection(config);
  connection.auth = new Auth(config.auth);
  connection.commands = new Commands(connection);

  try {
    // Connexion à Voyager
    console.log('\n📡 Connexion à Voyager...\n');
    await connection.connect();

    // Attendre que RoboTarget Manager Mode soit actif
    console.log('⏳ Attente de l\'activation du RoboTarget Manager Mode...\n');
    await new Promise(resolve => setTimeout(resolve, 3000));

    // ÉTAPE 1: Récupérer les Sets
    console.log('📋 ÉTAPE 1: Récupération des Sets...\n');
    const setsResult = await connection.commands.listSets();
    const sets = setsResult.ParamRet?.list || setsResult.parsed?.params?.list || [];

    if (sets.length === 0) {
      console.log('❌ Aucun Set trouvé!');
      process.exit(1);
    }

    console.log(`✅ ${sets.length} Set(s) trouvé(s):\n`);
    sets.forEach((set, idx) => {
      console.log(`   ${idx + 1}. ${set.setname} (${set.guid})`);
    });

    const selectedSet = sets.find(s => s.setname === "Test Claude Code") || sets[0];
    console.log(`\n✅ Set sélectionné: ${selectedSet.setname}`);
    console.log(`   GUID: ${selectedSet.guid}`);

    // ÉTAPE 2: Récupérer les BaseSequences
    console.log('\n📋 ÉTAPE 2: Récupération des BaseSequences...\n');
    const seqResult = await connection.commands.listBaseSequences();
    const sequences = seqResult.ParamRet?.list || seqResult.parsed?.params?.list || [];

    if (sequences.length === 0) {
      console.log('❌ Aucune BaseSequence trouvée!');
      process.exit(1);
    }

    console.log(`✅ ${sequences.length} BaseSequence(s) trouvée(s):\n`);
    sequences.slice(0, 5).forEach((seq, idx) => {
      console.log(`   ${idx + 1}. ${seq.nameseq} (${seq.guidbasesequence})`);
    });

    const selectedSeq = sequences[0];
    console.log(`\n✅ Séquence sélectionnée: ${selectedSeq.nameseq}`);
    console.log(`   GUID: ${selectedSeq.guidbasesequence}`);

    // ÉTAPE 3: Préparer la Target
    console.log('\n📋 ÉTAPE 3: Préparation de la Target...\n');

    const targetGuid = uuidv4();
    const targetParams = {
      // Identification (doc ligne 30-39)
      GuidTarget: targetGuid,
      RefGuidSet: selectedSet.guid,
      RefGuidBaseSequence: selectedSeq.guidbasesequence,
      TargetName: "M42 - Orion Nebula (API TEST)",
      Tag: "Nebula",
      DateCreation: Math.floor(Date.now() / 1000),

      // Position (doc ligne 41-50)
      RAJ2000: 5.588,      // RA en HEURES
      DECJ2000: -5.391,    // DEC en DEGRÉS
      PA: 0,
      Status: 0,           // 0 = Activé
      StatusOp: 0,         // 0 = Idle
      Priority: 2,         // Normal
      Note: "Target créée par script direct pour test documentation",

      // Overrides (doc ligne 52-65)
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

      // Contraintes (doc ligne 67-83)
      C_ID: uuidv4(),
      C_Mask: "BK",
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

      // Dynamiques (doc ligne 85-94)
      TType: 0,            // OBLIGATOIRE! 0=DSO
      TKey: "",
      TName: "",
      IsDynamicPointingOverride: false,
      DynamicPointingOverride: 0,
      DynEachX_Seconds: 0,
      DynEachX_Realign: false
    };

    console.log('✅ Paramètres préparés');
    console.log(`   Target: ${targetParams.TargetName}`);
    console.log(`   Set: ${selectedSet.setname}`);
    console.log(`   Séquence: ${selectedSeq.nameseq}`);

    // ÉTAPE 4: Envoyer la requête (la classe Commands va ajouter UID et MAC automatiquement)
    console.log('\n📤 ÉTAPE 4: Envoi de RemoteRoboTargetAddTarget...\n');

    const result = await connection.commands.addTarget(targetParams);

    console.log('='.repeat(80));
    console.log('\n🎉 RÉSULTAT\n');
    console.log('='.repeat(80));

    if (result.ParamRet?.ret === 'DONE') {
      console.log('✅✅✅ SUCCESS! Target créée avec succès!');
      console.log(`\n✅ VALIDATION FINALE: ParamRet.ret === "DONE" (doc ligne 109)`);
    } else {
      console.log('❌ Échec ou réponse inattendue');
      console.log(`   ParamRet.ret = "${result.ParamRet?.ret}"`);
    }

    console.log('\nRésultat complet:');
    console.log(JSON.stringify(result, null, 2));

    console.log('\n' + '='.repeat(80));
    console.log('📝 DONNÉES UTILISÉES');
    console.log('='.repeat(80));
    console.log(`Set: ${selectedSet.setname}`);
    console.log(`  GUID: ${selectedSet.guid}`);
    console.log(`\nBaseSequence: ${selectedSeq.nameseq}`);
    console.log(`  GUID: ${selectedSeq.guidbasesequence}`);
    console.log(`\nTarget: ${targetParams.TargetName}`);
    console.log(`  GUID: ${targetParams.GuidTarget}`);
    console.log('\n' + '='.repeat(80));

  } catch (error) {
    console.error('\n❌ ERREUR:', error.message);
    console.error(error.stack);
  } finally {
    connection.disconnect();
    // Wait a bit before exiting to ensure all logs are printed
    await new Promise(resolve => setTimeout(resolve, 2000));
    process.exit(0);
  }
}

main();
