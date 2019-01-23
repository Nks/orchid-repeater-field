<?php

namespace Nakukryskin\OrchidRepeaterField;

use Config;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Nakukryskin\OrchidRepeaterField\Commands\LinkCommand;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'platform');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('ORCHID_REPEATER_FIELD_PACKAGE_PATH')) {
            define('ORCHID_REPEATER_FIELD_PACKAGE_PATH', realpath(__DIR__ . '/../'));
        }

        $this->registerAssets();

        // Register the service the package provides.
        $this->app->singleton('repeater-field', function ($app) {
            return new RepeaterField;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['repeater-field'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the views.
        $this->publishes([
            ORCHID_REPEATER_FIELD_PACKAGE_PATH . '/resources/views' => base_path('resources/views/vendor/platform/fields'),
        ], 'platform');

        // Publishing assets.
        $this->publishes([
            ORCHID_REPEATER_FIELD_PACKAGE_PATH . '/resources/assets' => public_path('vendor/platform/repeater-field'),
        ], 'repeater-field.assets');

        // Publishing the translation files.
//        $this->publishes([
//            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/nakukryskin'),
//        ], 'orchidrepeaterfield.lang');

        // Registering package commands.
        $this->commands([
            LinkCommand::class
        ]);
    }

    /**
     * Adding styles and js to the platform
     * @throws \Exception
     */
    private function registerAssets(): void
    {
        Config::set('platform.resource.scripts', array_merge(config('platform.resource.scripts', []), [
            mix('/js/repeater.js', 'orchid_repeater')
        ]));

        Config::set('platform.resource.stylesheets', array_merge(config('platform.resource.stylesheets', []), [
            mix('css/repeater.css', 'orchid_repeater')
        ]));
    }
}
