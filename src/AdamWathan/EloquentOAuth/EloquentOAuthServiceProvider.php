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
			$users = new UserRepository($app['config']['auth.model']);
			$stateManager = new StateManager($app['session.store'], $app['request']);
			$oauth = new OAuthManager($app['auth'], $app['redirect'], $stateManager, $users, new IdentityRepository);
			$this->registerFacebook($oauth);
			$this->registerGithub($oauth);
			$this->registerGoogle($oauth);
			$this->registerLinkedIn($oauth);
			return $oauth;
		});
	}

	protected function registerFacebook($oauth)
	{
		$facebook = new FacebookProvider($this->app['config']['eloquent-oauth::providers.facebook'], new HttpClient, $this->app['request']);
		$oauth->registerProvider('facebook', $facebook);
	}

	protected function registerGithub($oauth)
	{
		$github = new GitHubProvider($this->app['config']['eloquent-oauth::providers.github'], new HttpClient, $this->app['request']);
		$oauth->registerProvider('github', $github);
	}

	protected function registerGoogle($oauth)
	{
		$google = new GoogleProvider($this->app['config']['eloquent-oauth::providers.google'], new HttpClient, $this->app['request']);
		$oauth->registerProvider('google', $google);
	}

	protected function registerLinkedIn($oauth)
	{
		$linkedin = new LinkedInProvider($this->app['config']['eloquent-oauth::providers.linkedin'], new HttpClient, $this->app['request']);
		$oauth->registerProvider('linkedin', $linkedin);
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
