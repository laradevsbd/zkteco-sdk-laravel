<?php

namespace Laradevsbd\Zkteco;

use Illuminate\Support\ServiceProvider;

class ZktecoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/views', 'zkteco');

        $this->mergeConfigFrom(
            __DIR__.'/config/zkteco.php', 'zkteco'
        );

        $this->publishes([
            __DIR__.'/config/zkteco.php' => config_path('zkteco.php'),
            __DIR__.'/views' => resource_path('views/vendor/zkteco'),
        ]);

        $this->publishes([
            __DIR__.'/assets' => public_path('vendor/zkteco'),
        ], 'public');

        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

}
