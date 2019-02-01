<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField;

use Orchid\Platform\Dashboard;
use Nakukryskin\OrchidRepeaterField\Commands\LinkCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * Perform post-registration booting of services.
     *
     * @param Dashboard $dashboard
     * @return void
     * @throws \Exception
     */
    public function boot(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;

        $this->registerResources();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'platform');

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
        if (! defined('ORCHID_REPEATER_FIELD_PACKAGE_PATH')) {
            define('ORCHID_REPEATER_FIELD_PACKAGE_PATH', realpath(__DIR__.'/../'));
        }

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
            ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/resources/views' => base_path('resources/views/vendor/platform/fields'),
        ], 'repeater-field.views');

        // Publishing assets.
        $this->publishes([
            ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/resources/assets' => public_path('vendor/platform/repeater-field'),
        ], 'repeater-field.assets');

        // Registering package commands.
        $this->commands([
            LinkCommand::class,
        ]);
    }

    /**
     * Registering resources.
     *
     * @throws \Exception
     */
    private function registerResources(): void
    {
        if (! file_exists(public_path('orchid_repeater'))) {
            return;
        }

        $this->dashboard->registerResource('scripts', mix('/js/repeater.js', 'orchid_repeater'));
        $this->dashboard->registerResource('stylesheets', mix('css/repeater.css', 'orchid_repeater'));
    }
}
