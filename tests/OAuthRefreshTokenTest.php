<?php

namespace FMCSSOClient\Tests;

use Carbon\Carbon;
use FMCSSOClient\Facades\SSOClient;
use FMCSSOClient\Token;
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
        Http::fakeSequence()
            ->push([
                'token_type'    => 'SomeType',
                'expires_in'    => $expiresAt->diffInSeconds(Carbon::now()),
                'access_token'  => 'example_access_token',
                'refresh_token' => 'example_refresh_token',
            ]);

        /** @var Token $token */
        $token = SSOClient::refreshToken(new Token('Example', 123, 'test', 'test_ref'));

        $this->assertInstanceOf(Token::class, $token);

        $this->assertEquals(0, $token->getExpiresAt()->diff($expiresAt)->s);
        $this->assertEquals('example_access_token', $token->getAccessToken());
        $this->assertEquals('example_refresh_token', $token->getRefreshToken());
        $this->assertEquals('SomeType ' . $token->getAccessToken(), $token->getAuthorizationHeader());
    }
}
