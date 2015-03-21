<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as HttpClient;
use SocialNorm\SocialNorm;
use SocialNorm\ProviderRegistry;
use SocialNorm\Request;
use SocialNorm\StateGenerator;

class EloquentOAuthServiceProvider extends ServiceProvider {

    protected $providerLookup = [
        'facebook' => 'SocialNorm\Facebook\FacebookProvider',
        'github' => 'SocialNorm\GitHub\GitHubProvider',
        'google' => 'SocialNorm\Google\GoogleProvider',
        'linkedin' => 'SocialNorm\LinkedIn\LinkedInProvider',
        'instagram' => 'SocialNorm\Instagram\InstagramProvider',
        'soundcloud' => 'SocialNorm\SoundCloud\SoundCloudProvider',
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->configureOAuthIdentitiesTable();
        $this->registerOAuthManager();
        $this->registerCommands();
    }

    protected function registerOAuthManager()
    {
        $this->app['adamwathan.oauth'] = $this->app->share(function ($app) {
            $providerRegistry = new ProviderRegistry;
            $session = new Session($app['session']);
            $request = new Request($app['request']->all());
            $stateGenerator = new StateGenerator;
            $socialnorm = new SocialNorm($providerRegistry, $session, $request, $stateGenerator);
            $this->registerProviders($socialnorm, $request);

            $users = new UserStore($app['config']['auth.model']);
            $authenticator = new Authenticator($app['Illuminate\Contracts\Auth\Guard'], $users, new IdentityStore);

            $oauth = new OAuthManager($app['redirect'], $authenticator, $socialnorm);
            return $oauth;
        });
    }

    protected function registerProviders($socialnorm, $request)
    {
        if (! $providerAliases = $this->app['config']['eloquent-oauth.providers']) {
            return;
        }

        foreach ($providerAliases as $alias => $config) {
            if (isset($this->providerLookup[$alias])) {
                $providerClass = $this->providerLookup[$alias];
                $provider = new $providerClass($config, new HttpClient, $request);
                $socialnorm->registerProvider($alias, $provider);
            }
        }
    }

    protected function configureOAuthIdentitiesTable()
    {
        OAuthIdentity::configureTable($this->app['config']['eloquent-oauth.table']);
    }

    /**
     * Registers some utility commands with artisan
     * @return void
     */
    public function registerCommands()
    {
        $this->app->bind('command.eloquent-oauth.install', 'AdamWathan\EloquentOAuth\Installation\InstallCommand');
        $this->commands('command.eloquent-oauth.install');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['adamwathan.oauth'];
    }

}
