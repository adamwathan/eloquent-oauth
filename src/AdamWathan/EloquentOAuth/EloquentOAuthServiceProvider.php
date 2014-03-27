<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Support\ServiceProvider;
use AdamWathan\EloquentOAuth\Providers\FacebookProvider;
use AdamWathan\EloquentOAuth\Providers\GitHubProvider;
use AdamWathan\EloquentOAuth\Providers\GoogleProvider;
use AdamWathan\EloquentOAuth\Providers\LinkedInProvider;
use Guzzle\Http\Client as HttpClient;

class EloquentOAuthServiceProvider extends ServiceProvider {

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
			$oauth = new OAuthManager($app['auth'], $app['config']['auth.model'], $app['redirect'], $app['session.store'], new IdentityRepository);
			$this->registerFacebook($oauth);
			$this->registerGithub($oauth);
			$this->registerGoogle($oauth);
			$this->registerLinkedIn($oauth);
			return $oauth;
		});
	}

	protected function registerFacebook($oauth)
	{
		$facebook = new FacebookProvider($this->app['config']['eloquent-oauth::providers.facebook'], new HttpClient);
		$oauth->registerProvider('facebook', $facebook);
	}

	protected function registerGithub($oauth)
	{
		$github = new GitHubProvider($this->app['config']['eloquent-oauth::providers.github'], new HttpClient);
		$oauth->registerProvider('github', $github);
	}

	protected function registerGoogle($oauth)
	{
		$google = new GoogleProvider($this->app['config']['eloquent-oauth::providers.google'], new HttpClient);
		$oauth->registerProvider('google', $google);
	}

	protected function registerLinkedIn($oauth)
	{
		$google = new LinkedInProvider($this->app['config']['eloquent-oauth::providers.linkedin'], new HttpClient);
		$oauth->registerProvider('linkedin', $google);
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
