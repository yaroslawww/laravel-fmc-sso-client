<?php

namespace FMCSSOClient\Tests\Fixtures;

use FMCSSOClient\SSOUser;

class CustomSSORouter extends CustomSSORouterWithoutScope
{
    protected function successUserCallback(SSOUser $user): mixed
    {
        return $user;
    }


    public function scopes(): array
    {
        return [ 'my:custom-scope', 'my:other-scope' ];
    }
}
