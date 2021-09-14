<?php

namespace FMCSSOClient\Http\Controllers;

use FMCSSOClient\Exceptions\CodeErrorException;
use FMCSSOClient\Facades\SSOClient;
use FMCSSOClient\SSOUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

abstract class SSORouter
{
    /**
     * Create default routes.
     *
     * @param string $prefix
     * @param string $namePrefix
     */
    public static function routes(string $prefix = 'fmc-sso', string $namePrefix = 'fmc-sso')
    {
        Route::prefix($prefix)->group(function () use ($namePrefix) {
            Route::get('/', [
                static::class,
                'redirectAction',
            ])->name("{$namePrefix}.redirect");
            Route::get('/callback', [
                static::class,
                'callbackAction',
            ])->name("{$namePrefix}.callback");
        });
    }

    /**
     * Step 1: redirect to authorize url.
     *
     * @return RedirectResponse
     */
    public function redirectAction()
    {
        return SSOClient::setScopes($this->scopes())
                        ->redirect();
    }

    /**
     * Step 2: get token and then user data.
     *
     * @return mixed
     */
    public function callbackAction(): mixed
    {
        try {
            return $this->successUserCallback(SSOClient::ssoUser()) ?? Redirect::back();
        } catch (CodeErrorException $e) {
            return $this->errorCodeCallback($e) ?? Redirect::back();
        }
    }

    /**
     * List of scopes.
     *
     * @return array
     */
    public function scopes(): array
    {
        return [];
    }

    /**
     * Callback to hand error response from code receiving.
     *
     * @param CodeErrorException $e
     *
     * @return mixed
     */
    protected function errorCodeCallback(CodeErrorException $e): mixed
    {
        throw $e;
    }

    /**
     * Callback to hand response with used data.
     *
     * @param SSOUser $user
     *
     * @return mixed
     */
    abstract protected function successUserCallback(SSOUser $user): mixed;
}
