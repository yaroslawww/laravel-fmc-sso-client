<?php

namespace FMCSSOClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method \FMCSSOClient\Facades\SSOClient setScopes( $scopes )
 */
class SSOClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \FMCSSOClient\SSOClient::class;
    }
}
