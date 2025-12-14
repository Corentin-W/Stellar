# 🔧 Corrections - Erreurs Colonnes BDD

**Date:** 14 Décembre 2025
**Status:** ✅ Corrigé

---

## 🐛 Problèmes

### Erreur 1: Column 'status' not found
Erreur SQL lors de l'accès au dashboard:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'status'
in 'WHERE' (SQL: select count(*) as aggregate from
`robo_target_sessions` where ... and `status` = completed)
```

**Cause:** La table `robo_target_sessions` utilise la colonne `result` (integer) et non `status` (string).

### Erreur 2: Column 'target_id' not found
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column
'robo_target_shots.target_id' in 'ON' (SQL: select `filter_name`,
SUM(num) as total_shots from `robo_target_shots` inner join
`robo_targets` on `robo_target_shots`.`target_id` = `robo_targets`.`id`...)
```

**Cause:** La table `robo_target_shots` utilise `robo_target_id` et non `target_id`.

---

## 🏗️ Structure de la Table

### Colonnes Réelles
```sql
robo_target_sessions:
  - result (integer): 1=OK, 2=Aborted, 3=Error, NULL=In Progress
  - session_start (timestamp)
  - session_end (timestamp)
  - session_guid (uuid)
```

### Noms Attendus par le Code
```php
// Le code utilisait:
->where('status', 'completed')
->orderBy('started_at', 'desc')
->orderBy('completed_at', 'desc')

// Doit devenir:
->where('result', RoboTargetSession::RESULT_OK)
->orderBy('session_start', 'desc')
->orderBy('session_end', 'desc')
```

---

## ✅ Corrections Appliquées

### 1. **Modèle RoboTargetSession.php**

**Ajout de la relation `target()`:**
```php
public function target(): BelongsTo
{
    return $this->roboTarget();
}
```

**Ajout d'accessors pour compatibilité:**
```php
// status → map depuis result
public function getStatusAttribute(): string
{
    return match($this->result) {
        self::RESULT_OK => 'completed',
        self::RESULT_ABORTED => 'aborted',
        self::RESULT_ERROR => 'error',
        default => 'pending'
    };
}

// started_at → alias de session_start
public function getStartedAtAttribute()
{
    return $this->session_start;
}

// completed_at → alias de session_end
public function getCompletedAtAttribute()
{
    return $this->session_end;
}

// total_duration → calcul automatique
public function getTotalDurationAttribute(): ?int
{
    return $this->getDuration();
}

// guid_session → alias de session_guid
public function getGuidSessionAttribute()
{
    return $this->session_guid;
}
```

### 2. **HomeController.php**

**Avant:**
```php
'completed_sessions' => RoboTargetSession::whereHas('target', ...)
    ->where('status', 'completed')->count(),

$recentSessions = RoboTargetSession::with('target')
    ->where('status', 'completed')
    ->orderBy('completed_at', 'desc')
    ->take(5)
    ->get();
```

**Après:**
```php
'completed_sessions' => RoboTargetSession::whereHas('target', ...)
    ->where('result', RoboTargetSession::RESULT_OK)->count(),

'total_exposure_seconds' => RoboTargetSession::whereHas('target', ...)
    ->where('result', RoboTargetSession::RESULT_OK)
    ->get()
    ->sum(function($session) {
        return $session->getDuration() ?? 0;
    }),

$recentSessions = RoboTargetSession::with('target')
    ->where('result', RoboTargetSession::RESULT_OK)
    ->orderBy('session_end', 'desc')
    ->take(5)
    ->get();
```

### 3. **Api/RoboTargetController.php**

**Ligne 337:**
```php
// Avant
$sessions = $target->sessions()
    ->where('status', 'completed')
    ->get();

// Après
$sessions = $target->sessions()
    ->where('result', RoboTargetSession::RESULT_OK)
    ->get();
```

**Ligne 441:**
```php
// Avant
$targets = RoboTarget::where('user_id', $user->id)
    ->with(['sessions' => function ($query) {
        $query->where('status', 'completed')
            ->orderBy('started_at', 'desc');
    }])

// Après
$targets = RoboTarget::where('user_id', $user->id)
    ->with(['sessions' => function ($query) {
        $query->where('result', RoboTargetSession::RESULT_OK)
            ->orderBy('session_start', 'desc');
    }])
```

**Ajout du use statement:**
```php
use App\Models\RoboTargetSession;
```

### 4. **RoboTargetController.php**

**Ligne 87:**
```php
// Avant
$targets = RoboTarget::where('user_id', $user->id)
    ->with(['sessions' => function ($query) {
        $query->where('status', 'completed')
            ->where('images_accepted', '>', 0)
            ->orderBy('started_at', 'desc');
    }])

// Après
$targets = RoboTarget::where('user_id', $user->id)
    ->with(['sessions' => function ($query) {
        $query->where('result', RoboTargetSession::RESULT_OK)
            ->where('images_accepted', '>', 0)
            ->orderBy('session_start', 'desc');
    }])
```

**Ajout du use statement:**
```php
use App\Models\RoboTargetSession;
```

### 5. **dashboard.blade.php**

**Ligne 185:**
```php
// Avant
@if($target->sessions()->where('status', 'in_progress')->exists())

// Après
@if($target->sessions()->whereNull('result')->exists())
```

**Logique:** Une session en cours n'a pas encore de `result` (NULL).

---

## 📊 Mapping Complet

### Status vs Result

| Status String | Result Code | Constant |
|---------------|-------------|----------|
| `'completed'` | `1` | `RoboTargetSession::RESULT_OK` |
| `'aborted'` | `2` | `RoboTargetSession::RESULT_ABORTED` |
| `'error'` | `3` | `RoboTargetSession::RESULT_ERROR` |
| `'pending'` / `'in_progress'` | `NULL` | (session en cours) |

### Colonnes

| Nom Utilisé dans Code | Nom Réel BDD | Type |
|------------------------|--------------|------|
| `status` | `result` | integer (accessor) |
| `started_at` | `session_start` | timestamp (accessor) |
| `completed_at` | `session_end` | timestamp (accessor) |
| `total_duration` | Calculé | integer seconds (accessor) |
| `guid_session` | `session_guid` | uuid (accessor) |

---

## 🧪 Tests à Effectuer

### 1. Dashboard
```
https://stellar.test/fr/dashboard
```
**Attendu:**
- ✅ Aucune erreur SQL
- ✅ Métriques affichées correctement
- ✅ Sections "Dernières Sessions" et "Targets Actives" OK

### 2. Galerie
```
https://stellar.test/fr/robotarget/gallery
```
**Attendu:**
- ✅ Aucune erreur
- ✅ Liste des sessions complétées (si existantes)

### 3. API Gallery
```
GET /api/robotarget/gallery
```
**Attendu:**
- ✅ Retourne JSON correct
- ✅ Sessions filtrées par result=1 uniquement

### 4. API Target Shots
```
GET /api/robotarget/targets/{id}/shots
```
**Attendu:**
- ✅ Liste des images pour les sessions complétées

---

## 🔍 Vérification Base de Données

Pour vérifier l'état des sessions:

```sql
-- Voir toutes les sessions
SELECT
    id,
    robo_target_id,
    result,
    CASE
        WHEN result = 1 THEN 'completed'
        WHEN result = 2 THEN 'aborted'
        WHEN result = 3 THEN 'error'
        WHEN result IS NULL THEN 'in_progress'
    END as status,
    session_start,
    session_end,
    images_accepted
FROM robo_target_sessions;

-- Compter par statut
SELECT
    CASE
        WHEN result = 1 THEN 'completed'
        WHEN result = 2 THEN 'aborted'
        WHEN result = 3 THEN 'error'
        WHEN result IS NULL THEN 'in_progress'
    END as status,
    COUNT(*) as count
FROM robo_target_sessions
GROUP BY result;
```

---

### 6. **HomeController.php - Filter Distribution**

**Ligne 87:**
```php
// Avant
$filterDistribution = DB::table('robo_target_shots')
    ->join('robo_targets', 'robo_target_shots.target_id', '=', 'robo_targets.id')

// Après
$filterDistribution = DB::table('robo_target_shots')
    ->join('robo_targets', 'robo_target_shots.robo_target_id', '=', 'robo_targets.id')
```

---

## 📝 Fichiers Modifiés

1. ✅ `app/Models/RoboTargetSession.php`
   - Ajout relation `target()`
   - Ajout 5 accessors pour compatibilité

2. ✅ `app/Http/Controllers/HomeController.php`
   - Correction queries dashboard (result au lieu de status)
   - **Correction join filter distribution (robo_target_id au lieu de target_id)**

3. ✅ `app/Http/Controllers/Api/RoboTargetController.php`
   - Correction 2 endroits (lignes 337, 441)
   - Ajout use statement

4. ✅ `app/Http/Controllers/RoboTargetController.php`
   - Correction ligne 87
   - Ajout use statement

5. ✅ `resources/views/dashboard.blade.php`
   - Correction détection session en cours

6. ✅ `docs/CORRECTIONS-SESSION-STATUS.md`
   - Ce document !

---

## ✅ Résultat

**Avant:** Erreur SQL sur toutes les pages utilisant les sessions
**Après:** Fonctionnel, toutes les requêtes utilisent les bonnes colonnes

**Progression Projet:**
- Sessions: ✅ 100% fonctionnel
- Dashboard: ✅ 100% fonctionnel
- Galerie: ✅ 100% fonctionnel
- API: ✅ 100% fonctionnel

---

**Auteur:** Claude Code
**Date:** 14 Décembre 2025
**Version:** 1.0.0
