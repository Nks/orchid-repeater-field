const path = require('path')
const mix = require('laravel-mix');

console.log(__dirname);
let webpackConfig = {
  resolve: {
    alias: {
      '~orchid': path.resolve(`${__dirname}`, '../../../vendor/orchid/platform/resources'),
    },
  },
  stats: {
      children: true,
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
