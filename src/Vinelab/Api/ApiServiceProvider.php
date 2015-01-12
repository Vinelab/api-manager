<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('vinelab/api-manager');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{


        // For Bindings:
        //--------------

        // ...


        // For Facade:
        //-------------

        // Register 'ApiFacade' instance container to our ApiFacade object
        $this->app['vinelab.api'] = $this->app->share(function()
            {
                return $this->app->make('Vinelab\Api\Api');
            });


        // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function()
            {
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
