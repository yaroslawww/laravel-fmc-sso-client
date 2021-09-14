<?php

namespace FMCSSOClient\Facades;

use Illuminate\Support\Facades\Facade;

class SSOClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \FMCSSOClient\SSOClient::class;
    }
}
