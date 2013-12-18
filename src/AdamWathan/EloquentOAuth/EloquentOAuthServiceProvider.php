<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Support\ServiceProvider;
use AdamWathan\EloquentOAuth\Providers\FacebookProvider;

class EloquentOauthServiceProvider extends ServiceProvider {

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
		$this->package('adamwathan/eloquent-oauth');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerOAuthManager();
	}

	protected function registerOAuthManager()
	{
		$this->app['adamwathan.oauth'] = $this->app->share(function($app)
		{
			$oauth = new OAuthManager($app['auth'], $app['config']['auth.model'], $app['redirect']);
			$oauth->registerProvider('facebook', new FacebookProvider($app['config']['eloquent-oauth::providers.facebook']));
			return $oauth;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('adamwathan.oauth');
	}

}