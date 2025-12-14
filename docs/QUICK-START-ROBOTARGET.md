# 🚀 QUICK START - RoboTarget

## ✅ Vérification de l'implémentation

### 1. Vérifier que tout est en place

```bash
# Vérifier les fichiers créés
ls -la app/Http/Middleware/RequireActiveSubscription.php
ls -la app/Http/Middleware/CheckFeatureAccess.php
ls -la app/Jobs/CheckStaleTargetsJob.php
ls -la app/Jobs/CreditMonthlyAllowanceJob.php
ls -la app/Http/Controllers/RoboTargetController.php
ls -la resources/views/dashboard/robotarget/index.blade.php
ls -la resources/views/dashboard/robotarget/show.blade.php
ls -la resources/views/dashboard/robotarget/create.blade.php
```

### 2. Créer un utilisateur de test avec abonnement

```bash
php artisan tinker
```

```php
// 1. Trouver ou créer un utilisateur
$user = User::first(); // Ou User::find(VOTRE_ID)

// 2. Créer un abonnement Nebula (plan intermédiaire)
$subscription = \App\Models\Subscription::create([
    'user_id' => $user->id,
    'plan' => 'nebula',
    'credits_per_month' => 60,
    'status' => 'active',
    'trial_ends_at' => now()->addDays(7),
]);

// 3. Ajouter des crédits
$user->increment('credits_balance', 60);

// 4. Vérifier
echo "Abonnement: " . $subscription->getPlanName() . "\n";
echo "Crédits: " . $user->credits_balance . "\n";
echo "Peut utiliser Nuit Noire: " . ($subscription->canUseMoonDown() ? 'OUI' : 'NON') . "\n";
```

### 3. Accéder à l'interface

1. **Se connecter avec votre utilisateur**
   - Aller sur: `http://localhost/fr/login` (ou `/en/login`)

2. **Voir le lien RoboTarget dans la sidebar**
   - Le lien "🎯 RoboTarget BETA" devrait apparaître juste après Dashboard
   - Badge violet/rose pour indiquer la version BETA

3. **Cliquer sur RoboTarget**
   - URL: `http://localhost/fr/robotarget`
   - Vous devriez voir la page de liste des targets (vide pour le moment)

### 4. Créer une target de test via l'API

```bash
# Récupérer un token Sanctum d'abord (si vous utilisez Sanctum)
# Ou utilisez une session web normale

curl -X POST http://localhost/api/robotarget/targets \
  -H "Content-Type: application/json" \
  -H "Cookie: VOTRE_SESSION_COOKIE" \
  -d '{
    "target_name": "M31 Andromeda",
    "ra_j2000": "00:42:44",
    "dec_j2000": "+41:16:09",
    "priority": 2,
    "c_moon_down": true,
    "c_alt_min": 30,
    "c_ha_start": -6,
    "c_ha_end": 6,
    "shots": [
      {
        "filter_index": 0,
        "filter_name": "Luminance",
        "exposure": 300,
        "num": 20,
        "gain": 100,
        "offset": 50,
        "bin": 1,
        "type": 0
      }
    ]
  }'
```

### 5. Vérifier la création

```bash
php artisan tinker
```

```php
// Lister les targets
\App\Models\RoboTarget::all();

// Détails d'une target
$target = \App\Models\RoboTarget::first();
echo "Nom: " . $target->target_name . "\n";
echo "Status: " . $target->status . "\n";
echo "GUID: " . $target->guid . "\n";
echo "Crédits estimés: " . $target->estimated_credits . "\n";
echo "Crédits bloqués: " . $target->credits_held . "\n";
```

---

## 🧪 TEST DU WORKFLOW COMPLET

### 1. Créer une target via l'interface web

1. Aller sur `http://localhost/fr/robotarget`
2. Cliquer sur "Nouvelle Target"
3. Remplir le formulaire en 4 étapes :
   - **Step 1:** Informations cible (nom, RA, DEC)
   - **Step 2:** Contraintes (priorité, altitude, moon down, HFD)
   - **Step 3:** Configuration shots (filtres, poses)
   - **Step 4:** Révision et estimation coût

### 2. Vérifier le hold des crédits

```php
$user = User::find(VOTRE_ID);
echo "Crédits avant: 60\n";
echo "Crédits après création: " . $user->credits_balance . "\n";

$target = RoboTarget::latest()->first();
echo "Crédits held: " . $target->credits_held . "\n";
```

### 3. Soumettre à Voyager (si Proxy actif)

1. Aller sur la page de détail de la target
2. Cliquer sur "▶️ Soumettre à Voyager"
3. La target devrait passer en statut `active`

### 4. Simuler une session complétée

```php
// Simuler un événement SessionComplete
$target = RoboTarget::where('status', 'active')->first();

$eventData = [
    'guid_target' => $target->guid,
    'guid_session' => \Str::uuid(),
    'session_start' => now()->subHours(2),
    'session_end' => now(),
    'result' => 1, // 1 = OK
    'hfd_mean' => 2.5,
    'images_captured' => 20,
    'images_accepted' => 18,
];

// Appeler le service
$service = app(\App\Services\RoboTargetService::class);
$service->handleSessionComplete($eventData);

// Vérifier
$target->refresh();
echo "Status: " . $target->status . "\n"; // Devrait être "completed"
echo "Crédits débités: " . $target->credits_charged . "\n";
```

---

## 🔧 SCHEDULER & JOBS

### Lancer le scheduler (développement)

```bash
# Dans un terminal
php artisan schedule:work
```

### Lancer la queue (développement)

```bash
# Dans un autre terminal
php artisan queue:work
```

### Tester CheckStaleTargetsJob

```php
// Créer une target "stale" (>48h)
$target = RoboTarget::create([
    'user_id' => 1,
    'target_name' => 'Test Stale',
    'ra_j2000' => '12:00:00',
    'dec_j2000' => '+45:00:00',
    'priority' => 1,
    'status' => 'active',
    'credits_held' => 10,
    'updated_at' => now()->subDays(3), // 3 jours
]);

// Dispatcher le job
\App\Jobs\CheckStaleTargetsJob::dispatch(48);

// Vérifier les logs
tail -f storage/logs/laravel.log
```

### Tester CreditMonthlyAllowanceJob

```php
// Dispatcher le job
\App\Jobs\CreditMonthlyAllowanceJob::dispatch();

// Vérifier que les crédits ont été ajoutés
$user = User::find(1);
echo "Crédits après renouvellement: " . $user->credits_balance . "\n";
```

---

## 🎯 URLS IMPORTANTES

| Page | URL | Description |
|------|-----|-------------|
| Liste targets | `/fr/robotarget` | Vue d'ensemble de toutes vos targets |
| Créer target | `/fr/robotarget/create` | Wizard de création 4 étapes |
| Voir target | `/fr/robotarget/{guid}` | Monitoring temps réel d'une target |
| API Liste | `/api/robotarget/targets` | API REST liste targets |
| API Créer | `/api/robotarget/targets` (POST) | API REST créer target |
| API Pricing | `/api/pricing/estimate` (POST) | Estimation coût |
| Plans | `/api/subscriptions/plans` | Liste des plans disponibles |

---

## 🐛 TROUBLESHOOTING

### Erreur "No subscription"

```php
// Vérifier l'abonnement
$user = User::find(VOTRE_ID);
$subscription = $user->subscription;

if (!$subscription) {
    echo "Pas d'abonnement - Créer un:\n";
    // Voir section 2 ci-dessus
}

if (!$subscription->isActive()) {
    echo "Abonnement inactif - Activer:\n";
    $subscription->update(['status' => 'active']);
}
```

### Erreur "Feature not available"

```php
// Vérifier les permissions
$subscription = User::find(1)->subscription;

echo "Plan: " . $subscription->plan . "\n";
echo "Max Priority: " . $subscription->getMaxPriority() . "\n";
echo "Peut MoonDown: " . ($subscription->canUseMoonDown() ? 'OUI' : 'NON') . "\n";
echo "Peut HFD: " . ($subscription->canAdjustHFD() ? 'OUI' : 'NON') . "\n";

// Si Stardust → Upgrade vers Nebula
$subscription->update([
    'plan' => 'nebula',
    'credits_per_month' => 60,
]);
```

### Le lien RoboTarget n'apparaît pas

1. Vider le cache :
```bash
php artisan view:clear
php artisan cache:clear
```

2. Vérifier que vous êtes connecté :
```bash
php artisan tinker
```
```php
auth()->check(); // Devrait retourner true
```

3. Vérifier le fichier sidebar :
```bash
grep -n "RoboTarget" resources/views/layouts/partials/astral-sidebar.blade.php
```

### Erreur 500 sur les vues

Vérifier les logs :
```bash
tail -f storage/logs/laravel.log
```

Si erreur de classe manquante :
```bash
composer dump-autoload
```

---

## 📊 STATISTIQUES & MONITORING

### Voir toutes les targets

```php
RoboTarget::with(['user', 'shots', 'sessions'])
    ->get()
    ->map(fn($t) => [
        'name' => $t->target_name,
        'status' => $t->status,
        'user' => $t->user->name,
        'shots' => $t->shots->count(),
        'sessions' => $t->sessions->count(),
    ]);
```

### Statistiques utilisateur

```php
$user = User::find(1);
$service = app(\App\Services\RoboTargetService::class);
$stats = $service->getUserStats($user);

print_r($stats);
```

### Voir les sessions

```php
\App\Models\RoboTargetSession::with('roboTarget')
    ->latest()
    ->get()
    ->map(fn($s) => [
        'target' => $s->roboTarget->target_name,
        'result' => $s->getResultLabel(),
        'hfd' => $s->hfd_mean,
        'images' => $s->images_captured . '/' . $s->images_accepted,
        'date' => $s->created_at,
    ]);
```

---

**🎉 Votre système RoboTarget est maintenant opérationnel !**

Pour toute question, consultez :
- `docs/IMPLEMENTATION-LARAVEL.md` - Documentation complète
- `docs/IMPLEMENTATION-RECAP.md` - Récapitulatif implémentation
- `docs/CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md` - Modèle économique

