<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Orchid\Platform\Dashboard;
use View;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends BaseServiceProvider
{
    protected Dashboard $dashboard;

    public function boot(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;

        $this->loadViewsFrom(ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/resources/views', 'platform');

        $this->registerResources()
            ->registerProviders()
            ->registerTranslations();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register()
    {
        if (! defined('ORCHID_REPEATER_FIELD_PACKAGE_PATH')) {
            define('ORCHID_REPEATER_FIELD_PACKAGE_PATH', realpath(__DIR__.'/../../'));
        }
    }

    public function registerProviders(): self
    {
        foreach ($this->provides() as $provide) {
            $this->app->register($provide);
        }

        return $this;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            RouteServiceProvider::class,
        ];
    }

    protected function bootForConsole()
    {
        $this->publishes([
            ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/resources/views' => base_path('resources/views/vendor/platform'),
        ], 'repeater-field.views');
    }

    private function registerResources(): self
    {
        $this->publishes([
            ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/public' => public_path('vendor/nakukryskin/orchid-repeater-field'),
        ], ['repeater-field.assets', 'laravel-assets']);

        View::composer('platform::app', function () {
            $this->dashboard
                ->registerResource('scripts', mix('/js/repeater.js', 'vendor/nakukryskin/orchid-repeater-field'))
                ->registerResource('stylesheets', mix('/css/repeater.css', 'vendor/nakukryskin/orchid-repeater-field'));
        });

        return $this;
    }

    private function registerTranslations(): void
    {
        $this->loadJsonTranslationsFrom(realpath(ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/resources/lang/'));
    }
}
