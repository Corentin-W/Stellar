# Frontend (CSS & JS) — Guide de mise en place et de contribution

Ce projet utilise Laravel 12 côté backend et Webpack Mix côté frontend, avec Tailwind CSS v4 et Alpine.js.

- Outils principaux: `laravel-mix` (Webpack), `tailwindcss` (v4), `@tailwindcss/forms`, `autoprefixer`, `alpinejs`.
- Points d’entrée: `resources/css/app.css`, `resources/js/app.js` (+ `resources/js/telescope.js`).
- Inclusion côté Blade: helpers `mix()` dans `resources/views/layouts/app.blade.php`.

## Sommaire

- Mise en place et commandes
- Structure CSS (Tailwind)
- Structure JS (Alpine)
- Intégration Blade
- Bonnes pratiques Tailwind v4
- Débogage & FAQ

---

## Mise en place et commandes

Prérequis: Node.js LTS (>= 18 recommandé) et NPM.

- Installer les dépendances: `npm ci` ou `npm install`
- Développement (watch): `npm run dev`
- Développement (HMR): `npm run hot`
- Build production (minifié + versionné): `npm run build`

Résultats de build:
- CSS: `public/css/app.css`
- JS: `public/js/app.js`
- Manifeste versionné: `public/mix-manifest.json`

Note: Si `npm run hot` est actif, Mix crée `public/hot` et le helper `mix()` chargera automatiquement les fichiers depuis le dev server.

---

## Structure CSS (Tailwind)

- Entrée principale: `resources/css/app.css`
- Config Tailwind: `tailwind.config.js`
- Chargement Tailwind v4: l’entrée commence par `@import "tailwindcss";`
- Plugins: `@tailwindcss/forms` est activé via la config Tailwind.

Organisation dans `app.css`:
- Base (`@layer base`)
  - Définition des variables CSS (tokens): couleurs, espacements, rayons, flous, ombres.
  - Styles globaux (`html`, `body`, scrollbar).
  - Mode sombre: surcharge des tokens sous `.dark { ... }`.
- Composants (`@layer components`)
  - Classes utilitaires composées: ex. `.btn-primary`, `.icon-btn`, `.card`, `.input`, `.notification-*`, layout dashboard (`.sidebar`, `.top-navbar`, `.page-content`, etc.).
  - Ces classes utilisent exclusivement des utilitaires Tailwind via `@apply`.
- Utilitaires (`@layer utilities`)
  - Helpers type `.gradient-text`, `.glow-primary`, `.animate-*`, `.text-responsive*`.
- Animations & Media queries spécifiques
  - Définies en fin de fichier (ex. `@keyframes float`, `@media print`).

Contenu scanné (purge): voir `tailwind.config.js` ⇒ `content: [...]` inclut Blade (`resources/views/**/*.blade.php`), JS (`resources/js/**/*.js`) et CSS (`resources/css/**/*.css`).

Ajouter un composant CSS:
1) Ouvrir `resources/css/app.css`.
2) Dans `@layer components`, créer une classe et utiliser `@apply` avec des utilitaires Tailwind uniquement.

Exemple:
```css
@layer components {
  .btn-tertiary {
    @apply inline-flex items-center gap-2 px-4 py-2 text-sm rounded-xl;
    @apply text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white;
    @apply hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition;
  }
}
```

Attention (Tailwind v4): `@apply` ne peut contenir que des utilitaires Tailwind. N’appliquez pas d’autres classes custom (ex. `@apply btn` ou `@apply badge`). Répétez les utilitaires nécessaires à la place.

Mode sombre:
- Piloté par la classe `dark` sur l’élément `html`.
- Utiliser `dark:` dans vos utilitaires ou surcharger les tokens sous `.dark { ... }`.

Autres CSS compilés par Mix:
- `resources/css/telescope.css`
- `resources/css/mobile-responsive.css`

---

## Structure JS (Alpine)

- Entrée principale: `resources/js/app.js`
  - Importe `./bootstrap` (Axios/CSRF, etc.) et Alpine.js.
  - Expose Alpine globalement: `window.Alpine = Alpine;` puis `Alpine.start()` (depuis `bootstrap.js` ou démarré implicitement via le layout).
  - Stores Alpine:
    - `darkMode`: persiste l’état, applique la classe `dark` à `<html>`, met à jour la meta `theme-color`.
    - `sidebar`: ouvre/ferme le panneau latéral, gère le scroll sur mobile.
    - `telescope`: état de connexion simulé, météo et sessions (démonstration UI).
    - `notifications`: liste et gestion des notifications (lues/non lues, ajout/suppression).

- Entrée supplémentaire: `resources/js/telescope.js`
  - Logique dédiée aux écrans « telescope » (statut, météo, sessions). Compilé séparément en `public/js/telescope.js`.

Utilisation côté Blade (extraits):
```html
<body x-data="{ sidebarOpen: false }" x-init="$store.darkMode.init()">
  <button @click="$store.darkMode.toggle()">Basculer le thème</button>
  <aside x-bind:class="{ 'open': $store.sidebar.isOpen }"></aside>
</body>
```

Inclure un bundle JS spécifique dans une vue:
```blade
@push('scripts')
  <script src="{{ mix('js/telescope.js') }}" defer></script>
@endpush
```

Ajouter un nouveau bundle JS:
1) Créez `resources/js/mon-module.js`.
2) Ajoutez dans `webpack.mix.js` une ligne `.js('resources/js/mon-module.js', 'public/js')`.
3) Rebuild: `npm run dev` (ou `npm run build`).
4) Incluez-le via `mix('js/mon-module.js')` dans votre Blade.

---

## Intégration Blade

Layout principal: `resources/views/layouts/app.blade.php`
- Chargement des assets:
```blade
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<script src="{{ mix('js/app.js') }}" defer></script>
```
- Stacks disponibles:
  - `@stack('head')` pour des balises additionnelles dans `<head>` (SEO, styles inline).
  - `@stack('scripts')` pour des scripts de page.

Exemples d’injection:
```blade
@push('head')
<style>.ma-page { color: rebeccapurple; }</style>
@endpush

@push('scripts')
<script>console.log('Ma page');</script>
@endpush
```

Note: Évitez `@push('styles')` si le layout n’a pas `@stack('styles')`. Utilisez `@push('head')` pour les styles inline.

---

## Bonnes pratiques Tailwind v4

- Utilisez des utilitaires Tailwind dans `@apply` (pas de classes custom).
- Évitez les noms de classes générés dynamiquement côté Blade/JS (non détectés par la purge). Préférez des variantes explicites ou une safelist.
- Si vous devez « safelister » des classes dynamiques, étendez `tailwind.config.js` (clé `safelist`) et rebuild.
- Groupez vos nouveaux composants dans `@layer components` pour garder un CSS ordonné.

---

## Débogage & FAQ

- « Je n’ai aucun style »
  - Vérifiez l’inclusion dans le layout via `mix('css/app.css')`.
  - Rebuild: `npm run dev` ou `npm run build`.
  - Inspectez `public/mix-manifest.json` et la présence de `public/css/app.css`.

- « Le build échoue avec Tailwind v4 »
  - Recherchez des `@apply` contenant des classes custom (interdit). Limitez-vous aux utilitaires Tailwind.
  - Vérifiez que les fichiers Blade/JS/CSS scannés par Tailwind sont bien listés dans `tailwind.config.js`.

- « Mes classes dynamiques sont purgées »
  - Évitez de construire des noms de classes à la volée. Si nécessaire, ajoutez-les en dur dans un commentaire `/* @preserve */` ou utilisez la safelist.

- « Comment ajouter un fichier CSS supplémentaire ? »
  - Ajoutez `.css('resources/css/mon.css', 'public/css')` dans `webpack.mix.js`.
  - Rebuild puis incluez `{{ mix('css/mon.css') }}` dans la vue cible.

---

## Références

- `webpack.mix.js`: configuration de build, PostCSS (Tailwind v4 via `@tailwindcss/postcss`).
- `tailwind.config.js`: contenu scanné, extensions du thème, plugin forms.
- `resources/css/app.css`: base, composants et utilitaires du design system.
- `resources/js/app.js`: Alpine stores globaux (dark mode, sidebar, notifications, telescope).
- `resources/views/layouts/app.blade.php`: inclusion des assets et stacks Blade.
