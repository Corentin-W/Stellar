const mix = require('laravel-mix');

// Configuration de base
mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
        require('autoprefixer'),
   ])
   .options({
        postCss: [
            require('tailwindcss'),
            require('autoprefixer'),
        ]
   });

// Configuration pour la production
if (mix.inProduction()) {
    mix.version()
       .options({
           terser: {
               terserOptions: {
                   compress: {
                       drop_console: true,
                   },
               },
           },
       });
} else {
    mix.sourceMaps();
}

// Hot reload pour le développement
mix.browserSync({
    proxy: 'localhost:8000',
    files: [
        'resources/views/**/*.php',
        'resources/js/**/*.js',
        'resources/css/**/*.css'
    ]
});

// Copies d'assets supplémentaires
mix.copyDirectory('resources/images', 'public/images');
