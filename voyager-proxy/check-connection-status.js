// Script pour vérifier l'état complet de la connexion Voyager

const checkStatus = async () => {
  try {
    console.log('🔍 VÉRIFICATION DE L\'ÉTAT DE LA CONNEXION VOYAGER\n');
    console.log('='.repeat(80));

    const response = await fetch('http://localhost:3003/api/dashboard/state');
    const data = await response.json();

    console.log('\n📊 État de la connexion:');
    console.log(`  Connecté: ${data.data ? 'OUI' : 'NON'}`);

    if (data.data) {
      console.log(`  Host: ${data.data.Host}`);
      console.log(`  Instance: ${data.data.Inst}`);
      console.log(`  Timestamp: ${data.data.Timestamp}`);
      console.log(`  Voyager Status: ${data.data.VOYSTAT}`);
    }

    console.log('\n🔐 Pour vérifier le Manager Mode, il faut regarder les logs du serveur.');
    console.log('   Recherchez dans les logs:');
    console.log('   - "✅ RoboTarget Manager Mode ACTIVE"');
    console.log('   - ou "❌ Failed to activate RoboTarget Manager Mode"');

    console.log('\n💡 DIAGNOSTIC:');

    if (!data.data) {
      console.log('   ❌ Voyager n\'est PAS connecté!');
      console.log('   → Vérifiez que Voyager est démarré et écoute sur le port 5950');
    } else {
      console.log('   ✅ Voyager est connecté');
      console.log('   ⚠️ Mais les commandes RoboTarget ne fonctionnent pas');
      console.log('   → Vérifiez que:');
      console.log('      1. RoboTarget Manager Mode a été activé au démarrage');
      console.log('      2. Voyager a une license Advanced ou Full');
      console.log('      3. Le SharedSecret dans .env correspond à celui configuré dans Voyager');
      console.log('      4. Le MAC Key dans .env est correct');
    }

    console.log('\n📋 RECOMMANDATION:');
    console.log('   Redémarrez le proxy avec la commande:');
    console.log('   cd voyager-proxy && npm run dev');
    console.log('   Et observez les logs pour voir si Manager Mode s\'active correctement.');

    console.log('\n' + '='.repeat(80));

  } catch (error) {
    console.error('\n❌ ERREUR:', error.message);
    console.error('Le proxy ne répond pas sur http://localhost:3003');
    console.error('Vérifiez qu\'il est bien démarré avec: npm run dev');
  }
};

checkStatus();
