const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/telescope.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
       require('tailwindcss'),  // Pas de @tailwindcss/postcss
       require('autoprefixer'),
   ])
   .postCss('resources/css/telescope.css', 'public/css', [
       require('tailwindcss'),
       require('autoprefixer'),
   ])
   .postCss('resources/css/mobile-responsive.css', 'public/css', [
       require('tailwindcss'),
       require('autoprefixer'),
   ])
   .options({
       processCssUrls: false
   })
   .sourceMaps();

if (mix.inProduction()) {
    mix.version();
}
