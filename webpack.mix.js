let mix = require('laravel-mix');

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

// Auth
mix
    .copyDirectory('resources/assets/img', 'public/img')
    .sass('resources/assets/sass/auth.scss', 'public/css')
    .version();

// Docs
mix
    .js('resources/assets/js/docs.js', 'public/js')
    .sass('resources/assets/sass/docs.scss', 'public/css')
    .version();
