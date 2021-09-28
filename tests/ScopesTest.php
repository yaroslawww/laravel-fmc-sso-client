<?php

namespace FMCSSOClient\Tests;

use FMCSSOClient\Facades\SSOClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;

class ScopesTest extends TestCase
{

    /** @test */
    public function by_default_scopes_are_empty()
    {
        $scopes = SSOClient::getScopes();

        $this->assertIsArray($scopes);
        $this->assertEmpty($scopes);
    }

    /** @test */
    public function set_scopes_as_string()
    {
        $scopes = SSOClient::setScopes('my:scope')->getScopes();

        $this->assertIsArray($scopes);
        $this->assertCount(1, $scopes);
        $this->assertEquals('my:scope', $scopes[0]);
    }

    /** @test */
    public function set_scopes_as_array()
    {
        $scopes = SSOClient::setScopes([
            'other:scope',
            'my:scope',
        ])->getScopes();

        $this->assertIsArray($scopes);
        $this->assertCount(2, $scopes);
        $this->assertEquals('my:scope', $scopes[1]);
    }

    /** @test */
    public function scope_overrides()
    {
        $scopes = SSOClient::setScopes([
            'other:scope',
            'my:scope',
        ])->getScopes();

        $this->assertIsArray($scopes);
        $this->assertCount(2, $scopes);
        $this->assertEquals('other:scope', $scopes[0]);

        $scopes = SSOClient::setScopes('new:scope')->getScopes();
        $this->assertIsArray($scopes);
        $this->assertCount(1, $scopes);
        $this->assertEquals('new:scope', $scopes[0]);
    }

    /** @test */
    public function scopes_are_unique()
    {
        $scopes = SSOClient::setScopes([
            'my:scope',
            'other:scope',
            'other:scope',
            'my:scope',
            'other:scope',
            'other:scope',
            'my:scope',
        ])->getScopes();

        $this->assertIsArray($scopes);
        $this->assertCount(2, $scopes);
        $this->assertEquals('my:scope', $scopes[0]);
        $this->assertEquals('other:scope', $scopes[1]);
    }

    /** @test */
    public function has_default_scopes()
    {
        Config::set('fmc-sso-client.scopes', [
            'scope_1',
            'scope:2',
        ]);
        $scopes = SSOClient::getScopes();

        $this->assertIsArray($scopes);
        $this->assertCount(2, $scopes);
        $this->assertEquals('scope_1', $scopes[0]);
        $this->assertEquals('scope:2', $scopes[1]);

        /** @var RedirectResponse $redirect */
        $redirect = SSOClient::redirect();
        $location = $redirect->getTargetUrl();

        $this->assertStringContainsString('scope=scope_1+scope%3A2', $location);
    }
}
