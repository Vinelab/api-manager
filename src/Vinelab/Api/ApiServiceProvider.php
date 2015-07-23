<?php

namespace Vinelab\Api;

/*
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/api.php' => config_path('api.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        // Register the 'Api' class using the container so that we can resolve it accordingly.
        $this->app['vinelab.api'] = $this->app->share(function () {
            return $this->app->make('Vinelab\Api\Api');
        });
        // Shortcut so developers don't need to add an alias in app/config/app.php
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Api', 'Vinelab\Api\ApiFacadeAccessor');
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('vinelab.api');
    }
}
