<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as HttpClient;

class EloquentOAuthServiceProvider extends ServiceProvider {

    protected $providerLookup = array(
        'facebook' => 'AdamWathan\\EloquentOAuth\\Providers\\FacebookProvider',
        'github' => 'AdamWathan\\EloquentOAuth\\Providers\\GitHubProvider',
        'google' => 'AdamWathan\\EloquentOAuth\\Providers\\GoogleProvider',
        'linkedin' => 'AdamWathan\\EloquentOAuth\\Providers\\LinkedInProvider',
        'instagram' => 'AdamWathan\\EloquentOAuth\\Providers\\InstagramProvider',
        'disqus' => 'AdamWathan\\EloquentOAuth\\Providers\\DisqusProvider',
        );

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
        $this->app['adamwathan.oauth'] = $this->app->share(function ($app) {
            $this->configureOAuthIdentitiesTable();
            $users = new UserStore($app['config']['auth.model']);
            $stateManager = new StateManager($app['session.store'], $app['request']);
            $authorizer = new Authorizer($app['redirect']);
            $authenticator = new Authenticator($app['auth'], $users, new IdentityStore);
            $oauth = new OAuthManager($authorizer, $authenticator, $stateManager, new ProviderRegistrar);
            $this->registerProviders($oauth);
            return $oauth;
        });
    }

    protected function registerProviders($oauth)
    {
        $providerAliases = $this->app['config']['eloquent-oauth::providers'];
        foreach ($providerAliases as $alias => $config) {
            if(isset($this->providerLookup[$alias])) {
                $providerClass = $this->providerLookup[$alias];
                $provider = new $providerClass($config, new HttpClient, $this->app['request']);
                $oauth->registerProvider($alias, $provider);
            }
        }
    }

    protected function configureOAuthIdentitiesTable()
    {
        OAuthIdentity::configureTable($this->app['config']['eloquent-oauth::table']);
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
