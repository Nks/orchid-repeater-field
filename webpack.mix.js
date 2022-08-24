const path = require('path')
const mix = require('laravel-mix');

let webpackConfig = {
  resolve: {
    alias: {
      '~orchid': path.resolve(`${__dirname}`, 'vendor/orchid/platform/resources'),
    },
  },
};

if (mix.inProduction()) {
  mix.version();

  mix.options({
    clearConsole: true,
    terser: {
      terserOptions: {
        compress: {
          drop_console: true,
        },
      },
    },
  });
}

mix.webpackConfig(webpackConfig);

mix.sass('resources/sass/app.scss', 'css/repeater.css').
  js('resources/js/app.js', 'js/repeater.js').
  setPublicPath('public').
  version();
