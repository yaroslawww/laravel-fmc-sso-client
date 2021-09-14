<?php

namespace FMCSSOClient\Tests\Fixtures;

use FMCSSOClient\Http\Controllers\SSORouter;
use FMCSSOClient\SSOUser;

class CustomSSORouterWithoutScope extends SSORouter
{
    protected function successUserCallback(SSOUser $user): mixed
    {
        return $user;
    }
}
