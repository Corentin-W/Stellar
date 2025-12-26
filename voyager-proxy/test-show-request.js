// Script pour créer une target avec de vraies données (Sets et BaseSequences)
import { v4 as uuidv4 } from 'uuid';

const showAddTargetRequest = async () => {
  try {
    console.log('🎯 CRÉATION DE TARGET AVEC DONNÉES RÉELLES\n');
    console.log('='.repeat(80));

    // ÉTAPE 1: Récupérer les Sets existants
    console.log('\n📋 ÉTAPE 1: Récupération des Sets...\n');

    const setsResponse = await fetch('http://localhost:3003/api/robotarget/sets');
    const setsData = await setsResponse.json();

    if (!setsData.success || !setsData.sets || setsData.sets.length === 0) {
      console.log('❌ Aucun Set trouvé!');
      return;
    }

    console.log(`✅ ${setsData.sets.length} Set(s) trouvé(s):\n`);
    setsData.sets.forEach((set, idx) => {
      console.log(`   ${idx + 1}. ${set.SetName} (${set.GuidSet})`);
    });

    // Utiliser le premier Set (ou "Test Claude Code" si disponible)
    let selectedSet = setsData.sets.find(s => s.SetName === "Test Claude Code") || setsData.sets[0];
    console.log(`\n✅ Set sélectionné: ${selectedSet.SetName}`);
    console.log(`   GUID: ${selectedSet.GuidSet}`);

    // ÉTAPE 2: Récupérer les BaseSequences
    console.log('\n📋 ÉTAPE 2: Récupération des BaseSequences...\n');

    const seqResponse = await fetch('http://localhost:3003/api/robotarget/base-sequences');
    const seqData = await seqResponse.json();

    if (!seqData.success || !seqData.sequences || seqData.sequences.length === 0) {
      console.log('❌ Aucune BaseSequence trouvée!');
      return;
    }

    console.log(`✅ ${seqData.sequences.length} BaseSequence(s) trouvée(s):\n`);
    seqData.sequences.slice(0, 5).forEach((seq, idx) => {
      console.log(`   ${idx + 1}. ${seq.NameSeq} (${seq.GuidBaseSequence})`);
    });

    const selectedSeq = seqData.sequences[0];
    console.log(`\n✅ Séquence sélectionnée: ${selectedSeq.NameSeq}`);
    console.log(`   GUID: ${selectedSeq.GuidBaseSequence}`);

    // ÉTAPE 3: Préparer les paramètres de la target selon la documentation
    console.log('\n📋 ÉTAPE 3: Préparation de la Target...\n');

    const targetUID = uuidv4();
    const targetGuid = uuidv4();

    const targetParams = {
      // Paramètres d'identification (obligatoires selon doc ligne 30-39)
      GuidTarget: targetGuid,
      RefGuidSet: selectedSet.GuidSet,           // ✅ GUID réel du Set
      RefGuidBaseSequence: selectedSeq.GuidBaseSequence, // ✅ GUID réel de la séquence
      TargetName: "M42 - Orion Nebula (TEST API)",
      Tag: "Nebula",
      DateCreation: Math.floor(Date.now() / 1000), // Epoch timestamp

      // Coordonnées (doc ligne 41-50)
      RAJ2000: 5.588,      // RA en HEURES (doc ligne 44)
      DECJ2000: -5.391,    // DEC en DEGRÉS (doc ligne 45)
      PA: 0,               // Position Angle (doc ligne 46)
      Status: 0,           // 0 = Activé (doc ligne 47)
      StatusOp: 0,         // 0 = Idle (doc ligne 48)
      Priority: 2,         // 0-4, 2=Normal (doc ligne 49)
      Note: "Target créée via API pour test documentation",

      // Overrides d'acquisition (doc ligne 52-65)
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
      C_Mask: "BK",        // B=Alt min, K=Moon Down (doc ligne 69-70)
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

      // Paramètres objets dynamiques (doc ligne 85-94)
      TType: 0,            // OBLIGATOIRE! 0=DSO (doc ligne 88)
      TKey: "",
      TName: "",
      IsDynamicPointingOverride: false,
      DynamicPointingOverride: 0,
      DynEachX_Seconds: 0,
      DynEachX_Realign: false
    };

    console.log('✅ Paramètres préparés');
    console.log(`   Target: ${targetParams.TargetName}`);
    console.log(`   Set: ${selectedSet.SetName}`);
    console.log(`   Séquence: ${selectedSeq.NameSeq}`);

    // ÉTAPE 4: Envoyer la requête
    console.log('\n📤 ÉTAPE 4: Envoi de la commande RemoteRoboTargetAddTarget...\n');

    const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        method: 'RemoteRoboTargetAddTarget',
        params: targetParams,
        macFormula: {
          sep1: '|| |',   // 1 espace (doc section 4, ligne 98-100)
          sep2: '||  |',  // 2 espaces
          sep3: '|| |'    // 1 espace
        }
      })
    });

    const result = await response.json();

    console.log('='.repeat(80));
    console.log('📦 REQUÊTE JSON-RPC COMPLÈTE ENVOYÉE À VOYAGER');
    console.log('='.repeat(80));
    console.log(JSON.stringify(result.command, null, 2));

    console.log('\n' + '='.repeat(80));
    console.log('🔐 CALCUL DU MAC (Section 4 de la documentation)');
    console.log('='.repeat(80));
    console.log(`\nFormule:      Secret|| |SessionKey||  |ID|| |UID`);
    console.log(`              (1 espace)  (2 espaces)  (1 espace)`);
    console.log(`\nMAC String:   ${result.macInfo.string}`);
    console.log(`\nÉtapes de calcul:`);
    console.log(`  1. SHA1 de la chaîne ci-dessus`);
    console.log(`  2. Convertir le hash en HEX`);
    console.log(`  3. Encoder le HEX en Base64`);
    console.log(`\nMAC (Base64): ${result.macInfo.mac}`);

    console.log('\n' + '='.repeat(80));
    console.log('📊 VÉRIFICATION PAR RAPPORT À LA DOCUMENTATION');
    console.log('='.repeat(80));

    const checks = [
      { item: 'TType présent', doc: 'Ligne 88 (OBLIGATOIRE)', status: targetParams.TType !== undefined },
      { item: 'RAJ2000 en heures', doc: 'Ligne 44', status: true },
      { item: 'DECJ2000 en degrés', doc: 'Ligne 45', status: true },
      { item: 'GuidTarget', doc: 'Ligne 34', status: !!targetParams.GuidTarget },
      { item: 'RefGuidSet (RÉEL)', doc: 'Ligne 35', status: targetParams.RefGuidSet === selectedSet.GuidSet },
      { item: 'RefGuidBaseSequence (RÉEL)', doc: 'Ligne 36', status: targetParams.RefGuidBaseSequence === selectedSeq.GuidBaseSequence },
      { item: 'MAC avec "1-2-1" espaces', doc: 'Section 4 (lignes 96-104)', status: true },
      { item: 'C_Mask défini', doc: 'Ligne 69', status: !!targetParams.C_Mask },
      { item: 'Priority (0-4)', doc: 'Ligne 49', status: targetParams.Priority >= 0 && targetParams.Priority <= 4 },
      { item: 'Status (0=actif)', doc: 'Ligne 47', status: targetParams.Status === 0 || targetParams.Status === 1 }
    ];

    checks.forEach(check => {
      const icon = check.status ? '✅' : '❌';
      console.log(`${icon} ${check.item.padEnd(35)} (Doc: ${check.doc})`);
    });

    console.log('\n' + '='.repeat(80));
    console.log('📋 RÉSULTAT DE LA COMMANDE');
    console.log('='.repeat(80));

    if (result.success) {
      console.log('✅ SUCCESS! Commande envoyée et réponse reçue\n');
      console.log('Résultat:');
      console.log(JSON.stringify(result.result, null, 2));

      if (result.result?.ParamRet?.ret === 'DONE') {
        console.log('\n✅✅✅ VALIDATION FINALE: ParamRet.ret === "DONE" (doc ligne 109)');
        console.log('🎉 LA TARGET A ÉTÉ CRÉÉE AVEC SUCCÈS!');
      } else {
        console.log(`\n⚠️  ParamRet.ret = "${result.result?.ParamRet?.ret}" (attendu: "DONE")`);
      }
    } else {
      console.log('❌ Échec ou timeout\n');
      if (result.error) {
        console.log(`Erreur: ${result.error}`);
      }
      if (result.result) {
        console.log('\nRésultat partiel:');
        console.log(JSON.stringify(result.result, null, 2));
      }
    }

    console.log('\n' + '='.repeat(80));
    console.log('📝 DONNÉES RÉELLES UTILISÉES');
    console.log('='.repeat(80));
    console.log(`Set: ${selectedSet.SetName}`);
    console.log(`  GUID: ${selectedSet.GuidSet}`);
    console.log(`\nBaseSequence: ${selectedSeq.NameSeq}`);
    console.log(`  GUID: ${selectedSeq.GuidBaseSequence}`);
    console.log(`\nTarget: ${targetParams.TargetName}`);
    console.log(`  GUID: ${targetParams.GuidTarget}`);
    console.log('\n' + '='.repeat(80));

  } catch (error) {
    console.error('\n❌ ERREUR:', error.message);
    console.error(error.stack);
  }
};

showAddTargetRequest();
