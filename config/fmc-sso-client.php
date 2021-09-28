<?php

/*
|--------------------------------------------------------------------------
| Fmc SSO configuration
|--------------------------------------------------------------------------
|
| You can also add configuration to  service config file app/config/services.php
| 'fmc-sso' => [
|     'client_id'    => env('FMC_SSO_CLIENT_ID'),
|     'client_secret' => env('FMC_SSO_CLIENT_SECRET'),
|     'redirect_url' => env('FMC_SSO_REDIRECT_URL'),
| ],
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    */
    'client_id'     => env('FMC_SSO_CLIENT_ID'),
    'client_secret' => env('FMC_SSO_CLIENT_SECRET'),
    'redirect_url'  => env('FMC_SSO_REDIRECT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Default scopes
    |--------------------------------------------------------------------------
    |
    */
    'scopes'        => array_filter(explode(',', env('FMC_SSO_SCOPES', ''))),

    /*
    |--------------------------------------------------------------------------
    | oAuth configuration
    |--------------------------------------------------------------------------
    |
    | Override default configuration
    |
    */
    'sso'           => [
        'ssl'            => env('FMC_SSO_SSL', 1),
        'domain'         => env('FMC_SSO_DOMAIN', 'sso.dentistry.co.uk'),
        'authorizePath'  => env('FMC_SSO_AUTHORIZE_PATH', '/oauth/authorize'),
        'tokenPath'      => env('FMC_SSO_TOKEN_PATH', '/oauth/token'),
        'userPath'       => env('FMC_SSO_USER_PATH', '/json/profile'),
        'scopeSeparator' => ' ',
        'useState'       => env('FMC_SSO_USE_STATE', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guzzle options
    |--------------------------------------------------------------------------
    |
    | Override default guzzle client options
    |
    */
    'guzzle'        => [],
];
