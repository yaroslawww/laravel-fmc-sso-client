<?php

namespace FMCSSOClient\Tests;

use FMCSSOClient\Facades\SSOClient;

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
}
