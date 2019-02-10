<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Orchid\Platform\Dashboard;

/**
 * Class ServiceProvider
 * @package OrchidRepeaterField
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Required version of orchid/platform package.
     */
    const REQUIRED_ORCHID_PLATFORM_VERSION = '3.8.1';

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

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'platform');

        $this->versionCompare()
            ->registerResources()
            ->registerTranslations();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     * @throws \Exception
     */
    public function register()
    {
        if (!defined('ORCHID_REPEATER_FIELD_PACKAGE_PATH')) {
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
    }

    /**
     * Registering resources.
     *
     * @throws \Exception
     */
    private function registerResources(): self
    {
        $this->dashboard->addPublicDirectory('repeater', ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/public/');

        \View::composer('platform::layouts.app', function () {
            \Dashboard::registerResource('scripts', orchid_mix('/js/repeater.js', 'repeater'))
                ->registerResource('stylesheets', orchid_mix('/css/repeater.css', 'repeater'));
        });

        return $this;
    }

    /**
     * Registering languages.
     *
     * @return ServiceProvider
     */
    private function registerTranslations(): self
    {
        $this->loadJsonTranslationsFrom(realpath(ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/resources/lang/'));

        return $this;
    }

    /**
     * Check that the package has correct orchid platform version.
     * @throws \Exception
     */
    private function versionCompare()
    {
        if (!version_compare(\Dashboard::version(), self::REQUIRED_ORCHID_PLATFORM_VERSION, '>=')) {
            throw new \Exception(sprintf(__('You cannot install %1$s because %1$s requires orchid/platform version %2$s or higher. You are running orchid/platform version %3$s.'),
                self::class, self::REQUIRED_ORCHID_PLATFORM_VERSION, \Orchid\Platform\Dashboard::VERSION));
        }

        return $this;
    }
}
