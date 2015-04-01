# Eloquent OAuth

[![Code Climate](https://codeclimate.com/github/adamwathan/eloquent-oauth/badges/gpa.svg)](https://codeclimate.com/github/adamwathan/eloquent-oauth)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/adamwathan/eloquent-oauth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/adamwathan/eloquent-oauth/?branch=master)
[![Build Status](https://api.travis-ci.org/adamwathan/eloquent-oauth.svg)](https://travis-ci.org/adamwathan/eloquent-oauth)

> Note: Check the [Laravel 4 branch](https://github.com/adamwathan/eloquent-oauth/tree/laravel-4) if you are using Laravel 4.

Eloquent OAuth is a package for Laravel 5 designed to make authentication against various OAuth providers *ridiculously* brain-dead simple. Specify your client IDs and secrets in a config file, run a migration and after that it's just two method calls and you have OAuth integration.

#### Video Walkthrough

[![Screenshot](https://cloud.githubusercontent.com/assets/4323180/6274884/ac824c48-b848-11e4-8e4d-531e15f76bc0.png)](https://vimeo.com/120085196)

#### Basic example

```php
// Redirect to Facebook for authorization
Route::get('facebook/authorize', function() {
    return OAuth::authorize('facebook');
});

// Facebook redirects here after authorization
Route::get('facebook/login', function() {
    
    // Automatically log in existing users
    // or create a new user if necessary.
    OAuth::login('facebook');

    // Current user is now available via Auth facade
    $user = Auth::user();

    return Redirect::intended();
});
```

#### Supported Providers

- Facebook
- GitHub
- Google
- LinkedIn
- Instagram
- SoundCloud

>Feel free to open an issue if you would like support for a particular provider, or even better, submit a pull request.

## Installation

#### Add this package using Composer

From the command line inside your project directory, simply type:

`composer require adamwathan/eloquent-oauth`

(Or you can manually edit `composer.json` by adding the following line under the `"require"` section:

`"adamwathan/eloquent-oauth": "~5.0"`

...then run `composer update` to download the package to your vendor directory.)

#### Update your config

Add the service provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...
    'AdamWathan\EloquentOAuth\EloquentOAuthServiceProvider',
    // ...
]
```

Add the facade to the `aliases` array in `config/app.php`:

```php
'aliases' => [
    // ...
    'OAuth' => 'AdamWathan\EloquentOAuth\Facades\OAuth',
    // ...
]
```

#### Publish the package configuration

Publish the configuration file and migrations by running the provided console command:

`php artisan eloquent-oauth:install`

Next, re-migrate your database:

`php artisan migrate`

> If you need to change the name of the table used to store OAuth identities, you can do so in the `eloquent-oauth` config file.

#### Configure the providers

Update your app information for the providers you are using in `config/eloquent-oauth.php`:

```php
'providers' => [
    'facebook' => [
        'id' => '12345678',
        'secret' => 'y0ur53cr374ppk3y',
        'redirect' => 'https://example.com/facebook/login'),
        'scope' => [],
    ]
]
```

> Each provider is preconfigured with the scope necessary to retrieve basic user information and the user's email address, so the scope array can usually be left empty unless you need specific additional permissions. Consult the provider's API documentation to find out what permissions are available for the various services.

All done!

> Eloquent OAuth is designed to integrate with Laravel's Eloquent authentication driver, so be sure you are using the `eloquent` driver in `app/config/auth.php`. You can define your actual `User` model however you choose and add whatever behavior you need, just be sure to specify the model you are using with its fully qualified namespace in `app/config/auth.php` as well.

## Usage

Authentication against an OAuth provider is a multi-step process, but I have tried to simplify it as much as possible.

### Authorizing with the provider

First you will need to define the authorization route. This is the route that your "Login" button will point to, and this route redirects the user to the provider's domain to authorize your app. After authorization, the provider will redirect the user back to your second route, which handles the rest of the authentication process.

To authorize the user, simply return the `OAuth::authorize()` method directly from the route.

```php
Route::get('facebook/authorize', function() {
    return OAuth::authorize('facebook');
});
```

### Authenticating within your app

Next you need to define a route for authenticating against your app with the details returned by the provider.

For basic cases, you can simply call `OAuth::login()` with the provider name you are authenticating with. If the user
rejected your application, this method will throw an `ApplicationRejectedException` which you can catch and handle
as necessary.

The `login` method will create a new user if necessary, or update an existing user if they have already used your application
before.

Once the `login` method succeeds, the user will be authenticated and available via `Auth::user()` just like if they
had logged in through your application normally.

```php
use \AdamWathan\EloquentOAuth\Exceptions\ApplicationRejectedException;
use \AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;

Route::get('facebook/login', function() {
    try {
        OAuth::login('facebook');
    } catch (ApplicationRejectedException $e) {
        // User rejected application
    } catch (InvalidAuthorizationCodeException $e) {
        // Authorization was attempted with invalid
        // code,likely forgery attempt
    }

    // Current user is now available via Auth facade
    $user = Auth::user();

    return Redirect::intended();
});
```

If you need to do anything with the newly created user, you can pass an optional closure as the second
argument to the `login` method. This closure will receive the `$user` instance and a `ProviderUserDetails`
object that contains basic information from the OAuth provider, including:

- User ID
- Nickname
- First Name
- Last Name
- Email
- Image URL
- Access Token

```php
OAuth::login('facebook', function($user, $details) {
    $user->nickname = $details->nickname;
    $user->name = $details->firstName . ' ' . $details->lastName;
    $user->profile_image = $details->imageUrl;
    $user->save();
});
```

> Note: The Instagram API does not allow you to retrieve the user's email address, so unfortunately that field will always be `null` for the Instagram provider.

### Advanced: Storing additional data

Remember: One of the goals of the Eloquent OAuth package is to normalize the data received across all supported providers, so that you can count on those specific data items (explained above) being available in the `$details` object.

But, each provider offers its own sets of additional data. If you need to access or store additional data beyond the basics of what Eloquent OAuth's default `ProviderUserDetails` object supplies, you need to do two things:

1. Request it from the provider, by extending its scope:

   Say for example we want to collect the user's gender when they login using Facebook.
   
   In the `config/eloquent-oauth.php` file, set the `[scope]` in the `facebook` provider section to include the `public_profile` scope, like this:
   
   ```php
      'scope' => ['email', 'public_profile'],
   ```
   
 > For available scopes with each provider, consult that provider's API documentation.

 > NOTE: By increasing the scope you will be asking the user to grant access to additional information. They will be informed of the scopes you're requesting. If you ask for too much unnecessary data, they may refuse. So exercise restraint when requesting additional scopes.

2. Now where you do your `OAuth::login`, store the to your `$user` object by accessing the `$details->raw()['KEY']` data:

 ```php
        OAuth::login('facebook', function($user, $details) (
            $user->gender = $details->raw()['gender']; // Or whatever the key is
            $user->save();
        });
 ```
 
 > TIP: You can see what the available keys are by testing with `dd($details->raw());` inside that same closure.

