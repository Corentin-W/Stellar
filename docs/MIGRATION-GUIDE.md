# 🔄 Guide de Migration - Transition vers RoboTarget

> **Guide complet pour la migration des utilisateurs existants**
> **Version:** 1.0.0
> **Date:** 12 Décembre 2025

---

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Ce qui change](#ce-qui-change)
3. [Impact sur les utilisateurs](#impact-sur-les-utilisateurs)
4. [Plan de migration](#plan-de-migration)
5. [Migration technique (Base de données)](#migration-technique)
6. [Communication utilisateurs](#communication-utilisateurs)
7. [FAQ Utilisateurs](#faq-utilisateurs)
8. [Timeline](#timeline)

---

## Vue d'ensemble

### Contexte

Stellar passe d'un **modèle de réservation horaire** à un **modèle RoboTarget automatisé**.

**Ancien modèle :**
- Réservation de créneaux horaires (20h-22h, etc.)
- Contrôle manuel du télescope en temps réel
- Dépendance à la disponibilité de l'utilisateur
- Crédits utilisés pour l'accès à l'équipement

**Nouveau modèle :**
- Configuration de cibles astrophotographiques
- Automatisation complète via RoboTarget de Voyager
- Optimisation automatique des conditions
- Abonnements mensuels avec crédits inclus

### Objectifs de la migration

1. ✅ **Conserver les crédits existants** des utilisateurs
2. ✅ **Migrer sans perte de service** (zéro downtime)
3. ✅ **Accompagner les utilisateurs** dans la transition
4. ✅ **Améliorer l'expérience utilisateur** globale
5. ✅ **Augmenter la satisfaction** (automatisation, qualité)

---

## Ce qui change

### Pour tous les utilisateurs

#### ❌ Système supprimé

| Fonctionnalité | Statut |
|----------------|--------|
| Réservation de créneaux horaires | ❌ Supprimé |
| Calendrier de disponibilité | ❌ Supprimé |
| Contrôle manuel en direct | ⚠️ Conservé (mode avancé) |
| Packs de crédits à l'unité | ⚠️ Optionnel (abonnement prioritaire) |

#### ✅ Nouveau système

| Fonctionnalité | Statut |
|----------------|--------|
| Abonnements mensuels (3 tiers) | ✅ Nouveau |
| RoboTarget automatisé | ✅ Nouveau |
| Target Planner (configurateur) | ✅ Nouveau |
| Dashboard temps réel | ✅ Nouveau |
| Optimisation météo/conditions | ✅ Nouveau |
| Garanties qualité (Quasar) | ✅ Nouveau |

### Tableau comparatif

| Critère | Ancien modèle | Nouveau modèle |
|---------|---------------|----------------|
| **Réservation** | Manuelle (créneaux) | Automatique (cibles) |
| **Présence requise** | Oui (contrôle direct) | Non (automatisé) |
| **Optimisation** | Utilisateur décide | Voyager optimise |
| **Tarification** | Packs de crédits | Abonnements mensuels |
| **Accès équipement** | Par créneaux | Par crédits (à l'usage) |
| **Qualité** | Variable | Garantie (selon plan) |

---

## Impact sur les utilisateurs

### Utilisateurs avec crédits existants

**Statut : Crédits conservés à 100%**

- Tous les crédits achetés avant la migration sont **conservés**
- Utilisables avec le nouveau système RoboTarget
- Expiration : même date que l'achat initial
- Conversion : 1 crédit ancien = 1 crédit nouveau

**Action requise :**
1. Choisir un abonnement mensuel (Stardust, Nebula, ou Quasar)
2. Les crédits existants s'ajoutent aux crédits de l'abonnement
3. Utilisation : crédits de l'abonnement en premier, puis crédits legacy

### Utilisateurs avec réservations futures

**Statut : Réservations honorées**

- Les réservations existantes (avant migration) sont **honorées**
- Accès maintenu en mode "contrôle manuel" pour ces sessions
- Communication individuelle pour report si nécessaire

**Action requise :**
1. Honorer les créneaux réservés
2. Après dernière réservation : migration vers RoboTarget
3. Proposition de compensation (crédits bonus) si report nécessaire

### Nouveaux utilisateurs (post-migration)

**Statut : Inscription directe sur nouveau modèle**

- Pas d'accès au système de réservation
- Onboarding sur RoboTarget uniquement
- Choix d'abonnement obligatoire

---

## Plan de migration

### Phase 1 : Préparation (J-30 à J-7)

#### Développement

- [x] Créer nouvelle architecture (Laravel, Proxy, Frontend)
- [x] Tests unitaires et intégration
- [ ] Tests end-to-end complets
- [ ] Déploiement en environnement de staging

#### Base de données

- [ ] Créer migrations Laravel
- [ ] Script de migration des données existantes
- [ ] Backup complet de la base de données
- [ ] Test de rollback

#### Communication

- [ ] Email d'annonce (J-30)
- [ ] Article de blog explicatif
- [ ] Vidéo démo du nouveau système
- [ ] FAQ complète
- [ ] Webinar de présentation (optionnel)

### Phase 2 : Communication (J-7 à J-1)

#### Email J-7 : Annonce de la migration

**Sujet :** 🚀 Stellar évolue : Découvrez RoboTarget et nos nouveaux abonnements

**Contenu :**
```
Bonjour [Prénom],

Nous sommes ravis de vous annoncer une évolution majeure de Stellar !

🤖 À partir du [DATE], Stellar adopte RoboTarget, le système d'astrophotographie
automatisée de Voyager. Fini les contraintes horaires, place à l'automatisation !

✨ Ce qui change pour vous :
- Plus besoin de réserver des créneaux
- Configuration de vos cibles en quelques clics
- Voyager optimise et capture automatiquement
- Récupération des images finalisées dans votre galerie

💳 Nouveaux abonnements :
- 🌟 Stardust : 29€/mois (20 crédits)
- 🌌 Nebula : 59€/mois (60 crédits)
- ⚡ Quasar : 119€/mois (150 crédits)

💰 Vos crédits actuels :
Rassurez-vous, vos [X] crédits restants sont conservés et utilisables
immédiatement avec RoboTarget !

📅 Réservations en cours :
Vos réservations existantes sont honorées. Vous pourrez continuer à utiliser
le mode manuel jusqu'à leur terme.

🎥 Découvrez RoboTarget en vidéo : [LIEN]
📖 Lire le guide complet : [LIEN]
❓ FAQ : [LIEN]

Nous restons à votre disposition pour toute question.

À très bientôt sur Stellar 2.0 !

L'équipe Stellar
```

#### Email J-1 : Rappel et choix d'abonnement

**Sujet :** ⏰ Dernières 24h - Choisissez votre abonnement Stellar

**Contenu :**
```
Bonjour [Prénom],

La migration vers RoboTarget a lieu demain !

🎯 Action requise avant demain 23h59 :
Choisissez votre abonnement sur votre tableau de bord : [LIEN DIRECT]

Si vous ne choisissez pas, nous vous attribuerons automatiquement :
- Stardust (29€) si vous aviez < 50 crédits
- Nebula (59€) si vous aviez ≥ 50 crédits

💡 Aide au choix :
- Débutant → Stardust
- Amateur confirmé → Nebula (RECOMMANDÉ)
- Expert/Professionnel → Quasar

Vos crédits actuels ([X] crédits) seront automatiquement ajoutés.

Des questions ? Répondez à cet email !

L'équipe Stellar
```

### Phase 3 : Migration (Jour J)

#### Planning (exemple)

**00h00 - 02h00** : Fenêtre de maintenance
- Mise en mode maintenance
- Backup final de la base de données
- Déploiement du nouveau code
- Exécution des migrations
- Tests de validation
- Ouverture du site

**02h00 - 08h00** : Monitoring intensif
- Surveillance des logs
- Support utilisateur réactif
- Corrections rapides si nécessaire

**08h00 - 12h00** : Email de confirmation
- Email à tous les utilisateurs confirmant la migration
- Lien vers le guide d'utilisation
- Invitation au webinar de démonstration

#### Email Jour J (08h00)

**Sujet :** ✅ Stellar RoboTarget est en ligne !

**Contenu :**
```
Bonjour [Prénom],

Stellar RoboTarget est désormais disponible ! 🎉

🎯 Votre compte :
- Abonnement : [PLAN] ([CREDITS] crédits/mois)
- Crédits conservés : [X] crédits
- Solde total actuel : [TOTAL] crédits

🚀 Premiers pas :
1. Connectez-vous : [LIEN]
2. Découvrez le Target Planner : [LIEN]
3. Configurez votre première cible : [LIEN GUIDE]

📺 Webinar de démonstration :
[DATE] à [HEURE] : Rejoignez-nous pour une démo en direct ! [LIEN]

💬 Besoin d'aide ?
- Guide utilisateur : [LIEN]
- FAQ : [LIEN]
- Support : support@stellar.app

Bon ciel étoilé !

L'équipe Stellar
```

### Phase 4 : Suivi (J+1 à J+30)

#### J+1 à J+7 : Support renforcé
- Support chat disponible 24/7
- Emails de suivi personnalisés
- Résolution rapide des problèmes

#### J+7 : Email de feedback
- Demande de retour d'expérience
- Sondage de satisfaction (NPS)
- Collecte des suggestions

#### J+30 : Bilan et optimisations
- Analyse des métriques
- Ajustements si nécessaire
- Communication des améliorations

---

## Migration technique

### 1. Migrations Laravel

```bash
# Créer toutes les migrations
php artisan make:migration create_subscriptions_table
php artisan make:migration create_robo_targets_table
php artisan make:migration create_robo_target_shots_table
php artisan make:migration create_robo_target_sessions_table
php artisan make:migration add_subscription_fields_to_users_table
php artisan make:migration migrate_legacy_credits_to_new_system
```

### 2. Script de migration des données

```php
<?php
// database/migrations/2025_12_12_000006_migrate_legacy_credits_to_new_system.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Migrer les crédits existants
        DB::statement('
            UPDATE users
            SET legacy_credits = credits_balance,
                credits_balance = 0
            WHERE credits_balance > 0
        ');

        // 2. Attribuer abonnement par défaut basé sur historique
        DB::statement("
            UPDATE users
            SET subscription_plan = CASE
                WHEN legacy_credits < 50 THEN 'stardust'
                WHEN legacy_credits >= 50 AND legacy_credits < 150 THEN 'nebula'
                ELSE 'quasar'
            END,
            subscription_status = 'trial',
            subscription_trial_ends_at = DATE_ADD(NOW(), INTERVAL 7 DAY)
            WHERE subscription_plan IS NULL
        ");

        // 3. Créer entrées de subscription
        DB::statement("
            INSERT INTO subscriptions (user_id, plan, status, credits_per_month, created_at, updated_at)
            SELECT
                id,
                subscription_plan,
                'trial',
                CASE subscription_plan
                    WHEN 'stardust' THEN 20
                    WHEN 'nebula' THEN 60
                    WHEN 'quasar' THEN 150
                END,
                NOW(),
                NOW()
            FROM users
            WHERE subscription_plan IS NOT NULL
        ");

        // 4. Créditer les crédits du premier mois
        DB::statement("
            UPDATE users u
            INNER JOIN subscriptions s ON u.id = s.user_id
            SET u.credits_balance = s.credits_per_month + u.legacy_credits
        ");

        // 5. Annuler les réservations futures (ou les marquer legacy)
        DB::statement("
            UPDATE bookings
            SET status = 'legacy_migration',
                notes = CONCAT(notes, ' [Migrated to RoboTarget]')
            WHERE start_time > NOW()
            AND status = 'confirmed'
        ");
    }

    public function down()
    {
        // Rollback : restaurer crédits
        DB::statement('
            UPDATE users
            SET credits_balance = legacy_credits,
                legacy_credits = 0
        ');

        // Supprimer subscriptions
        DB::table('subscriptions')->truncate();
    }
};
```

### 3. Commandes Artisan de migration

```php
<?php
// app/Console/Commands/MigrateToRoboTarget.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;
use App\Services\StripeService;

class MigrateToRoboTarget extends Command
{
    protected $signature = 'stellar:migrate-robotarget {--dry-run}';
    protected $description = 'Migrate users from booking system to RoboTarget';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting migration to RoboTarget...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $users = User::whereNull('subscription_plan')->get();

        $this->info("Found {$users->count()} users to migrate");

        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            try {
                $this->migrateUser($user, $dryRun);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\nError migrating user {$user->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();

        $this->info("\n\nMigration completed!");

        return 0;
    }

    protected function migrateUser(User $user, bool $dryRun)
    {
        // Déterminer le plan basé sur l'historique
        $legacyCredits = $user->credits_balance ?? 0;

        $plan = match(true) {
            $legacyCredits < 50 => 'stardust',
            $legacyCredits < 150 => 'nebula',
            default => 'quasar'
        };

        if ($dryRun) {
            $this->line("Would migrate user {$user->email} to {$plan} plan");
            return;
        }

        // Créer subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
            'credits_per_month' => Subscription::CREDITS_PER_PLAN[$plan]
        ]);

        // Migrer crédits
        $user->legacy_credits = $legacyCredits;
        $user->credits_balance = $subscription->credits_per_month + $legacyCredits;
        $user->save();

        // Envoyer email
        $user->notify(new \App\Notifications\MigrationComplete($subscription));
    }
}
```

### 4. Tests de migration

```php
<?php
// tests/Feature/MigrationTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_low_credits_gets_stardust()
    {
        $user = User::factory()->create(['credits_balance' => 30]);

        $this->artisan('stellar:migrate-robotarget');

        $user->refresh();

        $this->assertEquals('stardust', $user->subscription->plan);
        $this->assertEquals(50, $user->credits_balance); // 20 + 30 legacy
    }

    public function test_legacy_credits_are_preserved()
    {
        $user = User::factory()->create(['credits_balance' => 75]);

        $this->artisan('stellar:migrate-robotarget');

        $user->refresh();

        $this->assertEquals(75, $user->legacy_credits);
        $this->assertEquals(135, $user->credits_balance); // 60 (Nebula) + 75
    }

    public function test_migration_is_idempotent()
    {
        $user = User::factory()->create(['credits_balance' => 30]);

        $this->artisan('stellar:migrate-robotarget');
        $initialBalance = $user->fresh()->credits_balance;

        // Run again
        $this->artisan('stellar:migrate-robotarget');
        $finalBalance = $user->fresh()->credits_balance;

        $this->assertEquals($initialBalance, $finalBalance);
    }
}
```

---

## Communication utilisateurs

### Templates d'emails

#### 1. Email d'annonce (J-30)

Fichier : `resources/views/emails/migration/announcement.blade.php`

```blade
@component('mail::message')
# 🚀 Stellar évolue : Découvrez RoboTarget

Bonjour {{ $user->name }},

Nous sommes ravis de vous annoncer une évolution majeure de Stellar !

## Ce qui change

À partir du **{{ $migrationDate->format('d/m/Y') }}**, Stellar adopte **RoboTarget**,
le système d'astrophotographie automatisée de Voyager.

### Vos avantages

- 🤖 **Automatisation complète** - Plus besoin d'être présent
- 🌙 **Optimisation intelligente** - Voyager choisit les meilleures conditions
- 💳 **Abonnements flexibles** - 3 formules adaptées à vos besoins
- ⭐ **Garanties qualité** - Satisfaction garantie ou remboursé

## Vos crédits actuels

Bonne nouvelle : vos **{{ $user->credits_balance }} crédits** sont **conservés**
et utilisables immédiatement avec RoboTarget !

## Choisissez votre abonnement

@component('mail::panel')
**🌟 Stardust** - 29€/mois (20 crédits)
Idéal pour débuter

**🌌 Nebula** - 59€/mois (60 crédits) - RECOMMANDÉ
Pour amateurs confirmés

**⚡ Quasar** - 119€/mois (150 crédits)
Accès VIP + Garanties
@endcomponent

@component('mail::button', ['url' => $subscriptionUrl])
Choisir mon abonnement
@endcomponent

## Découvrez RoboTarget

@component('mail::button', ['url' => $videoUrl, 'color' => 'success'])
Voir la vidéo de démonstration
@endcomponent

Des questions ? Répondez simplement à cet email !

À très bientôt,
L'équipe Stellar

@endcomponent
```

#### 2. Email de migration complétée (Jour J)

Fichier : `resources/views/emails/migration/completed.blade.php`

```blade
@component('mail::message')
# ✅ Bienvenue sur Stellar RoboTarget !

Bonjour {{ $user->name }},

Votre compte a été migré avec succès ! 🎉

## Votre abonnement

@component('mail::panel')
**Plan :** {{ $subscription->getPlanName() }}
**Crédits mensuels :** {{ $subscription->credits_per_month }}
**Crédits conservés :** {{ $user->legacy_credits }}
**Solde actuel :** {{ $user->credits_balance }} crédits

@if($subscription->status === 'trial')
**Période d'essai :** {{ $subscription->trial_ends_at->format('d/m/Y') }}
@endif
@endcomponent

## Premiers pas

1. **Configurez votre première cible**

   @component('mail::button', ['url' => route('robotarget.planner')])
   Ouvrir le Target Planner
   @endcomponent

2. **Explorez le dashboard temps réel**

   Suivez vos cibles en cours d'exécution

3. **Consultez le guide utilisateur**

   @component('mail::button', ['url' => $guideUrl, 'color' => 'success'])
   Lire le guide complet
   @endcomponent

## Webinar de démonstration

Rejoignez-nous le **{{ $webinarDate->format('d/m/Y à H:i') }}** pour une
démonstration en direct et une session de questions/réponses !

@component('mail::button', ['url' => $webinarUrl, 'color' => 'primary'])
S'inscrire au webinar
@endcomponent

Bon ciel étoilé !
L'équipe Stellar

@endcomponent
```

---

## FAQ Utilisateurs

### Questions fréquentes

#### Q : Mes crédits actuels sont-ils perdus ?

**R :** Non ! Tous vos crédits sont **conservés à 100%** et utilisables immédiatement
avec RoboTarget. Ils s'ajoutent aux crédits de votre abonnement mensuel.

#### Q : Je dois choisir un abonnement même si j'ai des crédits ?

**R :** Oui. Le nouveau modèle fonctionne sur abonnement mensuel. Vos crédits existants
sont bonus et viennent en complément des crédits de l'abonnement.

#### Q : Que deviennent mes réservations futures ?

**R :** Elles sont **honorées**. Vous pourrez utiliser le mode contrôle manuel pour
ces sessions. Après votre dernière réservation, vous basculerez automatiquement sur
RoboTarget.

#### Q : Puis-je changer d'abonnement après ?

**R :** Oui, vous pouvez upgrader ou downgrader à tout moment. Les changements
prennent effet au prochain cycle de facturation.

#### Q : Le mode manuel disparaît complètement ?

**R :** Non. Le contrôle manuel reste disponible pour les utilisateurs avancés
(tous les plans). RoboTarget devient simplement le mode par défaut et recommandé.

#### Q : Comment fonctionne la période d'essai ?

**R :** Tous les utilisateurs migrés reçoivent **7 jours d'essai gratuit** sur leur
plan assigné. Vous pouvez annuler avant la fin sans être facturé.

#### Q : Puis-je revenir à l'ancien système ?

**R :** Non, l'ancien système de réservation est désactivé définitivement. RoboTarget
offre une meilleure expérience et de meilleures garanties.

#### Q : Qu'arrive-t-il si j'annule mon abonnement ?

**R :** Vous conservez l'accès jusqu'à la fin de la période payée. Vos crédits
legacy restent disponibles même après annulation.

---

## Timeline

```
J-30 ┃ 📧 Email d'annonce + Article de blog
     ┃ 🎥 Publication vidéo démo
     ┃
J-21 ┃ 📺 Webinar de présentation (optionnel)
     ┃
J-14 ┃ 📧 Email de rappel + Incitation au choix d'abonnement
     ┃
J-7  ┃ 📧 Email d'urgence : "Plus qu'une semaine"
     ┃ 💬 Messages in-app pour utilisateurs non-migrés
     ┃
J-1  ┃ 📧 Email final : "Dernières 24h"
     ┃ 🔔 Notifications push
     ┃
J    ┃ 🚀 MIGRATION (00h00 - 02h00)
     ┃ ✅ Site en ligne avec RoboTarget
     ┃ 📧 Email de confirmation (08h00)
     ┃
J+1  ┃ 💬 Support renforcé 24/7
     ┃ 📊 Monitoring intensif
     ┃
J+7  ┃ 📧 Email de feedback + Sondage NPS
     ┃ 📺 Webinar de démonstration
     ┃
J+30 ┃ 📊 Bilan complet
     ┃ 📧 Email de remerciement
```

---

## Checklist finale

### Avant migration (J-1)

- [ ] Backup complet de la base de données
- [ ] Tests end-to-end passants
- [ ] Environnement de staging validé
- [ ] Emails programmés
- [ ] Support team briefée
- [ ] Monitoring configuré
- [ ] Rollback plan documenté

### Jour J

- [ ] Migration exécutée avec succès
- [ ] Tests de validation OK
- [ ] Emails de confirmation envoyés
- [ ] Monitoring actif
- [ ] Support disponible

### Après migration (J+7)

- [ ] Tous les utilisateurs migrés
- [ ] Zéro incident critique
- [ ] Feedback collecté
- [ ] Ajustements identifiés
- [ ] Documentation utilisateur finalisée

---

## Support et contacts

- **Email support :** support@stellar.app
- **Chat en direct :** Disponible dans l'application
- **Documentation :** https://docs.stellar.app
- **Status page :** https://status.stellar.app

---

**Guide de migration complété ! ✅**

*Dernière mise à jour : 12 Décembre 2025*
