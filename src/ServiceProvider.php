<?php

namespace FMCSSOClient;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/fmc-sso-client.php' => config_path('fmc-sso-client.php'),
            ], 'config');


            $this->commands([
                //
            ]);
        }

        $this->app->singleton(SSOClient::class, function ($app) {
            return new SSOClient(
                config('services.fmc-sso.client_id', config('fmc-sso-client.client_id')),
                config('services.fmc-sso.client_secret', config('fmc-sso-client.client_secret')),
                config('services.fmc-sso.redirect_url', config('fmc-sso-client.redirect_url')),
                config('services.fmc-sso.sso', config('fmc-sso-client.sso', [])),
                config('services.fmc-sso.guzzle', config('fmc-sso-client.guzzle', [])),
            );
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fmc-sso-client.php', 'fmc-sso-client');
    }
}
