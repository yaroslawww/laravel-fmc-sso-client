<?php

namespace FMCSSOClient\Tests;

use Carbon\Carbon;
use FMCSSOClient\Exceptions\CodeErrorException;
use FMCSSOClient\Exceptions\InvalidResponseUrlException;
use FMCSSOClient\Exceptions\InvalidTokenResponseException;
use FMCSSOClient\Exceptions\InvalidUserResponseException;
use FMCSSOClient\Facades\SSOClient;
use FMCSSOClient\SSOUser;
use FMCSSOClient\Token;
use Illuminate\Support\Facades\Http;

class OAuthStep2Test extends TestCase
{

    /** @test */
    public function code_should_be_present_in_request()
    {
        $this->expectException(InvalidResponseUrlException::class);
        SSOClient::ssoUser();
    }

    /** @test */
    public function exception_on_code_error()
    {
        $this->app['request']->merge([
            'error' => 'access_denied',
        ]);

        $this->expectException(CodeErrorException::class);
        $this->expectExceptionMessage('access_denied');
        SSOClient::ssoUser();
    }

    /** @test */
    public function token_response_should_contain_valid_response()
    {
        $this->app['request']->merge([
            'code' => 'my_code',
        ]);

        Http::fake(function ($request) {
            return Http::response('Not Valid', 200);
        });

        $this->expectException(InvalidTokenResponseException::class);
        SSOClient::ssoUser();
    }

    /** @test */
    public function token_response_should_be_successful()
    {
        $this->app['request']->merge([
            'code' => 'my_code',
        ]);

        Http::fake(function ($request) {
            return Http::response([], 400);
        });

        $this->expectException(InvalidTokenResponseException::class);
        $this->expectExceptionMessage('Token response not valid.');
        SSOClient::ssoUser();
    }

    /** @test */
    public function user_response_should_contain_valid_response()
    {
        $this->app['request']->merge([
            'code' => 'my_code',
        ]);

        Http::fakeSequence()
            ->push([
                'token_type'    => 'SomeType',
                'expires_in'    => 1000,
                'access_token'  => 'example_access_token',
                'refresh_token' => 'example_refresh_token',
            ])
            ->push([
                'data' => [

                ],
            ]);

        $this->expectException(InvalidUserResponseException::class);
        SSOClient::ssoUser();
    }

    /** @test */
    public function user_response_should_be_successful()
    {
        $this->app['request']->merge([
            'code' => 'my_code',
        ]);

        $expiresAt = Carbon::now()->addSeconds(1000);
        Http::fakeSequence()
            ->push([
                'token_type'    => 'SomeType',
                'expires_in'    => $expiresAt->diffInSeconds(Carbon::now()),
                'access_token'  => 'example_access_token',
                'refresh_token' => 'example_refresh_token',
            ])
            ->push([
                'data' => [
                    'id'          => 123,
                    'title'       => 'Dev.',
                    'first_name'  => 'Web',
                    'last_name'   => 'Tester',
                    'email'       => 'tester@web.home',
                    'example_key' => 'example_val',
                ],
            ]);

        $ssoUser = SSOClient::ssoUser();

        $this->assertInstanceOf(SSOUser::class, $ssoUser);

        $ssoUser2 = SSOClient::ssoUser();

        $this->assertEquals($ssoUser2, $ssoUser);

        $this->assertEquals(123, $ssoUser->getId());
        $this->assertEquals('Dev.', $ssoUser->getTitle());
        $this->assertEquals('Web', $ssoUser->getFirstName());
        $this->assertEquals('Tester', $ssoUser->getLastName());
        $this->assertEquals('tester@web.home', $ssoUser->getEmail());
        $this->assertEquals('Dev. Web Tester', $ssoUser->getName());

        $this->assertIsArray($ssoUser->getRaw());
        $this->assertNotEmpty($ssoUser->getRaw());

        $this->assertTrue($ssoUser->offsetExists('example_key'));
        $this->assertFalse($ssoUser->offsetExists('example_other_key'));

        $this->assertEquals('example_val', $ssoUser->offsetGet('example_key'));
        $this->assertNull($ssoUser->offsetGet('example_other_key'));

        $ssoUser->offsetSet('example_other_key', 'other_val');
        $this->assertTrue($ssoUser->offsetExists('example_other_key'));
        $this->assertEquals('other_val', $ssoUser->offsetGet('example_other_key'));

        $ssoUser->offsetUnset('example_other_key');
        $this->assertFalse($ssoUser->offsetExists('example_other_key'));
        $this->assertNull($ssoUser->offsetGet('example_other_key'));

        $token = $ssoUser->token();

        $this->assertInstanceOf(Token::class, $token);

        $this->assertEquals(0, $token->getExpiresAt()->diff($expiresAt)->s);
        $this->assertEquals('example_access_token', $token->getAccessToken());
        $this->assertEquals('example_refresh_token', $token->getRefreshToken());

        /** @var Token $token2 */
        $token2 = unserialize(serialize($token));

        $this->assertNotEquals($token2, $token);
        $this->assertEquals($token2->getExpiresAt()->getTimestamp(), $token->getExpiresAt()->getTimestamp());
        $this->assertEquals($token2->getAccessToken(), $token->getAccessToken());
        $this->assertEquals($token2->getRefreshToken(), $token->getRefreshToken());
        $this->assertEquals($token2->getAuthorizationHeader(), $token->getAuthorizationHeader());
    }



    /** @test */
    public function error_on_invalid_response()
    {
        $this->app['request']->merge([
            'code' => 'my_code',
        ]);

        Http::fakeSequence()
            ->push([
                'token_type'    => 'SomeType',
                'expires_in'    => -10,
                'access_token'  => 'example_access_token',
                'refresh_token' => 'example_refresh_token',
            ]);

        $this->expectException(InvalidTokenResponseException::class);
        $this->expectExceptionMessage('Token not valid or expired');
        SSOClient::ssoUser();
    }
}
