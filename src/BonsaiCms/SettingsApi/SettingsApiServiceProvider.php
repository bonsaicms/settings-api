<?php

namespace BonsaiCms\SettingsApi;

use Illuminate\Support\ServiceProvider;

class SettingsApiServiceProvider extends ServiceProvider
{
    /**
     * Register the settings package;
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../../config/settings-api.php', 'settings-api'
        );

        $implementations = config('settings-api.implementations');

        // Requests
        $this->app->bind(Contracts\ReadSettingsRequestContract::class, $implementations[Contracts\ReadSettingsRequestContract::class]);
        $this->app->bind(Contracts\WriteSettingsRequestContract::class, $implementations[Contracts\WriteSettingsRequestContract::class]);

        // Responses
        $this->app->bind(Contracts\ReadSettingsResponseContract::class, $implementations[Contracts\ReadSettingsResponseContract::class]);
        $this->app->bind(Contracts\WriteSettingsResponseContract::class, $implementations[Contracts\WriteSettingsResponseContract::class]);
    }

    /**
     * Bootstrap the settings package;
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../../config/settings-api.php' => config_path('settings-api.php'),
        ], 'settings-api');

        $this->loadRoutesFrom(__DIR__.'/../../../routes/routes.php');
    }
}
