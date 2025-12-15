# 📅 Configuration du Scheduler Laravel

## Vue d'ensemble

Le scheduler Laravel exécute automatiquement des tâches périodiques :

1. **robotarget:check-stale** - Toutes les heures
   - Vérifie les cibles RoboTarget expirées (> 48h en status "executing")
   - Rembourse les crédits hold si nécessaire
   - Envoie des notifications aux utilisateurs

2. **subscription:renew-credits** - Le 1er de chaque mois à minuit
   - Renouvelle les crédits mensuels pour tous les abonnements actifs
   - Ajoute les crédits selon le plan (Stardust: 20, Nebula: 60, Quasar: 150)
   - Envoie des emails de confirmation

---

## Installation Production

### 1. Configurer le Cron Job

Sur le serveur de production :

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne :
* * * * * cd ~/sites/stellarloc.com && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Vérifier la configuration

```bash
# Lister le crontab
crontab -l

# Vérifier les jobs planifiés Laravel
cd ~/sites/stellarloc.com
php artisan schedule:list
```

### 3. Surveiller les logs

```bash
# Voir les logs du scheduler
tail -f storage/logs/laravel.log | grep -E "(CheckStaleTargetsJob|CreditMonthlyAllowanceJob)"
```

---

## Développement Local

### Lister les jobs planifiés

```bash
php artisan schedule:list
```

**Sortie attendue :**
```
0 * * * *  robotarget:check-stale ................. Next Due: dans X minutes
0 0 1 * *  subscription:renew-credits ............ Next Due: dans X jours
```

### Tester un job manuellement

```bash
# Tester le job de vérification des cibles stale
php artisan schedule:test --name=robotarget:check-stale

# Tester le job de renouvellement des crédits
php artisan schedule:test --name=subscription:renew-credits
```

### Mode continu (développement)

```bash
# Lance le scheduler en boucle (comme en production)
php artisan schedule:work
```

Cela exécutera automatiquement les jobs selon leur planning. Utile pour tester localement.

---

## Exécution Manuelle des Jobs

Si tu veux forcer l'exécution immédiate d'un job :

```bash
# Vérifier les cibles stale maintenant
php artisan schedule:run --name=robotarget:check-stale

# Renouveler les crédits maintenant (attention : va vraiment ajouter des crédits !)
php artisan schedule:run --name=subscription:renew-credits
```

---

## Dépannage

### Le scheduler ne s'exécute pas

**Vérifier que le cron job est actif :**
```bash
crontab -l
```

**Vérifier les logs cron :**
```bash
# Ubuntu/Debian
grep CRON /var/log/syslog

# CentOS/RHEL
tail -f /var/log/cron
```

**Vérifier les permissions :**
```bash
cd ~/sites/stellarloc.com
ls -la storage/logs/
# Les fichiers doivent être writables par l'utilisateur web
```

### Les jobs échouent

**Voir les erreurs détaillées :**
```bash
tail -f storage/logs/laravel.log
```

**Tester manuellement :**
```bash
php artisan schedule:test --name=robotarget:check-stale
```

**Vérifier la file d'attente :**
```bash
php artisan queue:failed
```

---

## Optimisations

### Éviter les chevauchements

Les jobs sont déjà configurés avec :
- `onOneServer()` - S'exécute sur un seul serveur si tu as plusieurs serveurs
- `withoutOverlapping()` - Empêche l'exécution si le job précédent est encore en cours

### Notifications en cas d'échec

Ajouter dans `routes/console.php` :

```php
Schedule::job(new CheckStaleTargetsJob(48))
    ->hourly()
    ->onFailure(function () {
        // Envoyer email ou notification Slack
        \Log::error('CheckStaleTargetsJob failed');
    });
```

### Limiter le temps d'exécution

```php
Schedule::job(new CheckStaleTargetsJob(48))
    ->hourly()
    ->timeout(600); // Max 10 minutes
```

---

## Monitoring Production

### Installer Laravel Horizon (optionnel)

Pour monitorer les jobs en temps réel :

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Puis accéder à : `https://stellarloc.com/horizon`

### Alternative : Logs simples

Surveiller l'activité :

```bash
# En temps réel
tail -f storage/logs/laravel.log

# Filtrer par job
tail -f storage/logs/laravel.log | grep "CheckStaleTargetsJob"
```

---

## Checklist de Production

- [ ] Cron job configuré (`crontab -e`)
- [ ] Cron job vérifié (`crontab -l`)
- [ ] Jobs listés (`php artisan schedule:list`)
- [ ] Logs accessibles (`storage/logs/` writable)
- [ ] Queue workers actifs (Supervisor)
- [ ] Notifications configurées (emails)
- [ ] Monitoring en place

---

## Références

- [Laravel Scheduling Documentation](https://laravel.com/docs/12.x/scheduling)
- Fichier de configuration : `routes/console.php`
- Jobs concernés :
  - `app/Jobs/CheckStaleTargetsJob.php`
  - `app/Jobs/CreditMonthlyAllowanceJob.php`

---

**Dernière mise à jour :** 15 Décembre 2025
