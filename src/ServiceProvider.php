<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField;

use Config;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Nakukryskin\OrchidRepeaterField\Commands\LinkCommand;
use Orchid\Platform\Dashboard;

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

        $this->registerResources($this->getResources());

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
     * Registering resources
     *
     * TODO: https://github.com/Nks/orchid-repeater-field/issues/1
     *
     * @param array $resources
     * @throws \Exception
     */
    private function registerResources(array $resources): void
    {
        if (array_has($resources, 'scripts')) {
            Config::set('platform.resource.scripts',
                array_merge(config('platform.resource.scripts', []), $resources['scripts']));
        }

        if (array_has($resources, 'stylesheets')) {
            Config::set('platform.resource.stylesheets',
                array_merge(config('platform.resource.stylesheets', []), $resources['stylesheets']));
        }
    }

    /**
     * Adding styles and js to the platform
     * @throws \Exception
     */
    private function getResources(): array
    {
        if (!file_exists(public_path('orchid_repeater'))) {
            return [];
        }

        return [
            'scripts' => [
                mix('/js/repeater.js', 'orchid_repeater')
            ],
            'stylesheets' => [
                mix('css/repeater.css', 'orchid_repeater')
            ]
        ];
    }
}
