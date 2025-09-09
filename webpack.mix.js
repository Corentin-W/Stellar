const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/telescope.js', 'public/js')
   .css('resources/css/app.css', 'public/css')
   .css('resources/css/telescope.css', 'public/css')
   .css('resources/css/mobile-responsive.css', 'public/css')
   .options({
       processCssUrls: false,
       postCss: [
           require('autoprefixer'),
           require('postcss-css-variables'),
       ]
   })
   .version()
   .sourceMaps();

// Enable hot reloading for development
if (mix.inProduction()) {
    mix.version();
} else {
    mix.options({
        hmrOptions: {
            host: 'localhost',
            port: 8080
        }
    });
}

// Copy assets
mix.copy('resources/images', 'public/images')
   .copy('resources/fonts', 'public/fonts');

// Bundle vendor libraries
mix.extract(['alpine']);