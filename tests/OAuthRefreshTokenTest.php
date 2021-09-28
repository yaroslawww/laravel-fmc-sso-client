<?php

namespace FMCSSOClient\Tests;

use Carbon\Carbon;
use FMCSSOClient\Facades\SSOClient;
use FMCSSOClient\Token;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class OAuthRefreshTokenTest extends TestCase
{

    /** @test */
    public function return_null_if_refresh_empty()
    {
        $this->assertNull(
            SSOClient::refreshToken(new Token('Example', 123, 'test', ''))
        );
    }

    /** @test */
    public function return_null_if_expired()
    {
        $this->assertNull(
            SSOClient::refreshToken(new Token('Example', -10, 'test', 'test_ref'))
        );
    }

    /** @test */
    public function return_token_on_success()
    {
        $expiresAt = Carbon::now()->addSeconds(1000);
        Http::fake(function (Request $request) use ($expiresAt) {
            $this->assertArrayNotHasKey('scope', $request->data());

            return Http::response([
                'token_type'    => 'SomeType',
                'expires_in'    => $expiresAt->diffInSeconds(Carbon::now()),
                'access_token'  => 'example_access_token',
                'refresh_token' => 'example_refresh_token',
            ]);
        });

        /** @var Token $token */
        $token = SSOClient::refreshToken(new Token('Example', 123, 'test', 'test_ref'));

        $this->assertInstanceOf(Token::class, $token);

        $this->assertEquals(0, $token->getExpiresAt()->diff($expiresAt)->s);
        $this->assertEquals('example_access_token', $token->getAccessToken());
        $this->assertEquals('example_refresh_token', $token->getRefreshToken());
        $this->assertEquals('SomeType ' . $token->getAccessToken(), $token->getAuthorizationHeader());
    }

    /** @test */
    public function can_have_other_scopes()
    {
        $expiresAt = Carbon::now()->addSeconds(1000);
        Http::fake(function (Request $request) use ($expiresAt) {
            $this->assertArrayHasKey('scope', $request->data());
            $this->assertEquals('my_scope_1 my:scope:2', $request->data()['scope']);

            return Http::response([
                'token_type'    => 'SomeType',
                'expires_in'    => $expiresAt->diffInSeconds(Carbon::now()),
                'access_token'  => 'example_access_token',
                'refresh_token' => 'example_refresh_token',
            ]);
        });

        /** @var Token $token */
        $token = SSOClient::refreshToken(new Token('Example', 123, 'test', 'test_ref'), ['my_scope_1', 'my:scope:2']);

        $this->assertInstanceOf(Token::class, $token);

        $this->assertEquals(0, $token->getExpiresAt()->diff($expiresAt)->s);
        $this->assertEquals('example_access_token', $token->getAccessToken());
        $this->assertEquals('example_refresh_token', $token->getRefreshToken());
        $this->assertEquals('SomeType ' . $token->getAccessToken(), $token->getAuthorizationHeader());
    }
}
