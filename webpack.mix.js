const mix = require("laravel-mix");
const path = require("path");

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

if (!mix.inProduction()) {
  mix.sourceMaps();
} else {
  mix.version();
}

mix
  .js("resources/js/main.js", "public/js/main.js")
  .vue();
  // .sass("resources/sass/main.scss", "public/css");

mix.webpackConfig({
  resolve: {
    alias: {
      extensions: [".js", ".json", ".vue"],
      vue$: "vue/dist/vue.runtime.esm.js",
      "@": path.resolve(__dirname, "resources/js/"),
    },
  },
  devServer: {
    allowedHosts: "auto",
  },
});

mix.options({
  hmrOptions: {
    host: "http://localhost",
    port: "8000",
  },
});
