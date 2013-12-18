# Eloquent OAuth

Eloquent OAuth is a package for Laravel 4 designed to make authentication against various OAuth providers *ridiculously* brain-dead simple. Specify your app keys/secrets in a config file, run a migration and from then on it's two method calls and you have OAuth integration.

## Usage

Authentication against an OAuth provider is a multi-step process, but I have tried to simplify it as much as possible.

Firstly, you will need to define two routes. The first route is what your "Login" button will point to, and this route redirects the user to the provider's domain to authorize your app. After authorization, the provider will redirect the user back to your second route, which handles the rest of the authentication process.

```php
// This route redirects the user to Facebook to authorize
Route::get('facebook/authorize', function() {
	return OAuth::authorize('facebook');
});

// Facebook redirects the user back to this route to finish authenticating
Route::get('facebook/login', function() {
	try {
		OAuth::login('facebook');
	} catch (ApplicationRejectedException $e) {
		// User rejected app
	}

	// Can now retrieve the user
	$user = Auth::user();
});
```

## Supported Providers

- Facebook

*The package is still in it's early infancy obviously. Support will be added for other providers as time goes on.*

## Installation

I'll have this up on Packagist soon, but for the mean time you can manually add this repository to your `composer.json`
and require `"adamwathan/eloquent-oauth": "dev-master"`.

Add the service provider to the `providers` array in `app/config/app.php`:

```php
'providers' => array(
	// ...
	'AdamWathan\EloquentOAuth\EloquentOAuthServiceProvider',
	// ...
)
```

Add the facade to the `aliases` array in `app/config/app.php`:

```php
'aliases' => array(
	// ...
	'OAuth' => AdamWathan\EloquentOAuth\Facades\OAuth',
	// ...
)
```

Publish the configuration file:

`php artisan config:publish adamwathan/eloquent-oauth`

Update your app information for the providers you are using in `app/config/packages/adamwathan/eloquent-oauth/config.php`:

```php
'providers' => array(
	'facebook' => array(
		'id' => '12345678',
		'secret' => 'y0ur53cr374ppk3y',
		'redirect' => URL::to('facebook/login'),
		'scope' => '',
	)
)
```

Run the migration:

`php artisan migrate --package="adamwathan/eloquent-oauth"`

All done!




