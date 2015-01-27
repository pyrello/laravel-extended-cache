<?php namespace Pyrello\LaravelExtendedCache;

use Illuminate\Cache\CacheServiceProvider;

class ServiceProvider extends CacheServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        parent::register();

        $this->app->bindShared('cache', function($app)
        {
            return new CacheManager($app);
        });
	}

}
