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
		$this->package('vinelab/api');
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







        // For Facade:
        //-------------

        // Register 'ApiFacade' instance container to our ApiFacade object
        $this->app['api'] = $this->app->share(function()
            {
                return $this->app->make('Vinelab\Api\ApiManager');
            });

        // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function()
            {
                $loader = \Illuminate\Foundation\AliasLoader::getInstance();
                $loader->alias('Api', 'Vinelab\Api\ApiFacadeAccessor');
            });


        // For config files:
        //------------------
        // registering the package (for the config files)
        $this->package("vinelab/api");

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
