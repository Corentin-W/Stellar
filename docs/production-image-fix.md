# Fix Images en Production

## Problème
Les images des templates RoboTarget ne se chargent pas en production.

## Solution

### 1. Vérifier le lien symbolique storage

Sur le serveur de production, exécute:

```bash
cd ~/sites/stellarloc.com
php artisan storage:link
```

Tu devrais voir: `The [public/storage] link has been connected to [storage/app/public].`

Si le lien existe déjà, tu verras: `The [public/storage] link already exists.`

### 2. Vérifier les permissions

```bash
# Permissions du dossier storage
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Si nécessaire, ajuste le propriétaire (remplace www-data par ton utilisateur web)
# chown -R www-data:www-data storage
# chown -R www-data:www-data bootstrap/cache
```

### 3. Vérifier que APP_ENV est bien en production

Dans ton fichier `.env` sur le serveur:

```env
APP_ENV=production
APP_URL=https://stellarloc.com
```

### 4. Pull les derniers changements

```bash
git pull origin v1
```

### 5. Vider tous les caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 6. Tester l'API

```bash
curl https://stellarloc.com/api/target-templates | jq '.data[0].preview_image'
```

Tu devrais voir une URL en HTTPS comme:
`"https://stellarloc.com/storage/target-templates/xxxxx.png"`

### 7. Vérifier qu'il y a des images dans storage

```bash
ls -la storage/app/public/target-templates/
```

Si le dossier est vide, tu dois uploader des images via l'interface admin.

### 8. Tester l'accès direct à une image

Une fois que tu as une URL d'image de l'API, teste-la:

```bash
curl -I https://stellarloc.com/storage/target-templates/xxxxx.png
```

Tu devrais voir `HTTP/1.1 200 OK`

## Changements apportés

1. **AppServiceProvider.php** - Force HTTPS en production
2. **TargetTemplateController.php** - Utilise `url()` au lieu de `asset()` pour générer les URLs
3. **package.json** - Déplace laravel-echo et pusher-js vers dependencies

## Si ça ne marche toujours pas

Vérifie dans la console du navigateur (F12) :
1. L'URL de l'API appelée
2. Les URLs des images retournées
3. Les erreurs de chargement (404, 403, mixed content, etc.)

Partage-moi ces informations pour plus d'aide.
