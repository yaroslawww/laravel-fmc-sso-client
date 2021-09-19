# FMC SSO client for laravel framework.
![Packagist License](https://img.shields.io/packagist/l/yaroslawww/laravel-fmc-sso-client?color=%234dc71f)
[![Build Status](https://scrutinizer-ci.com/g/yaroslawww/laravel-fmc-sso-client/badges/build.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-fmc-sso-client/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/yaroslawww/laravel-fmc-sso-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-fmc-sso-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yaroslawww/laravel-fmc-sso-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-fmc-sso-client/?branch=master)

[![](./assets/img/fmc_logo.jpg)](https://fmc.co.uk/)

If your application uses multiple oAuth providers, it's easier to use a service-independent package
like [laravel/socialite](https://github.com/laravel/socialite)

## Installation

Install the package via composer:

```bash
composer require yaroslawww/laravel-fmc-sso-client
```

Set environment variables

```dotenv
# .env
FMC_SSO_CLIENT_ID="123456"
FMC_SSO_CLIENT_SECRET="RvYvYTDtMDt..."
FMC_SSO_REDIRECT_URL="https://my.app.home/fmc-sso/callback"

```

Optionally you can publish the config file with:

```bash
php artisan vendor:publish --provider="FMCSSOClient\ServiceProvider" --tag="config"
```

## Usage

The fastest way to add functionality is to create a class that extends `SSORouter`

```injectablephp
use FMCSSOClient\Http\Controllers\SSORouter;
use FMCSSOClient\SSOUser;

class FMCSSORouter extends SSORouter
{
    protected function successUserCallback(SSOUser $user): mixed
    {
        $user->getId();
        $user->getName();
        $user->getEmail();
        // add/update database user based on returned object.
    }
    
    // Optional you can add custom scopes
    // public function scopes(): array
    // {
    //     return ['my:custom-scope', 'my:other-scope'];
    // }
}
```

Then add routes:

```injectablephp
// /routes/web.php
Route::middleware( [ 'guest', /*...*/ ] )->group( fn() => \App\Domain\FMCSSO\FMCSSORouter::routes() );
```

This is it. The default login route is `http://my.domain.home/fmc-sso`

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
