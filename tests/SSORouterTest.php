<?php

namespace FMCSSOClient\Tests;

use FMCSSOClient\Exceptions\CodeErrorException;
use FMCSSOClient\SSOUser;
use FMCSSOClient\Tests\Fixtures\CustomSSORouter;
use FMCSSOClient\Tests\Fixtures\CustomSSORouterWithoutScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class SSORouterTest extends TestCase
{

    /** @test */
    public function router_can_create_routes()
    {
        CustomSSORouter::routes('route-prefix', 'name-pref');

        $routes = Route::getRoutes();
        $paths  = $names = [];
        /** @var \Illuminate\Routing\Route $route */
        foreach ($routes as $route) {
            $paths[] = $route->uri();
            $names[] = $route->getName();
        }
        $this->assertTrue(in_array('name-pref.redirect', $names));
        $this->assertTrue(in_array('name-pref.callback', $names));
        $this->assertTrue(in_array('route-prefix', $paths));
        $this->assertTrue(in_array('route-prefix/callback', $paths));
    }

    /** @test */
    public function router_can_create_valid_step1()
    {
        $router   = new CustomSSORouter();
        $redirect = $router->redirectAction();
        $location = $redirect->getTargetUrl();

        // Configured to skip state
        $this->assertStringNotContainsString('state', $location);
        $this->assertStringContainsString('response_type=code', $location);
        $this->assertStringContainsString('client_id=test_client_id', $location);
        $this->assertStringContainsString('redirect_uri=test_redirect_url', $location);
        $this->assertStringContainsString('scope=my%3Acustom-scope+my%3Aother-scope', $location);

        $router   = new CustomSSORouterWithoutScope();
        $redirect = $router->redirectAction();
        $location = $redirect->getTargetUrl();

        // Configured to skip state
        $this->assertStringNotContainsString('state', $location);
        $this->assertStringContainsString('client_id=test_client_id', $location);
        $this->assertStringContainsString('redirect_uri=test_redirect_url', $location);
        $this->assertStringNotContainsString('scope=', $location);
    }

    /** @test */
    public function router_can_create_valid_step2()
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
                    'id'          => 123,
                    'title'       => 'Dev.',
                    'first_name'  => 'Web',
                    'last_name'   => 'Tester',
                    'email'       => 'tester@web.home',
                    'example_key' => 'example_val',
                ],
            ]);

        $router = new CustomSSORouter();
        $user   = $router->callbackAction();

        $this->assertInstanceOf(SSOUser::class, $user);
    }

    /** @test */
    public function router_can_handle_code_error_callback()
    {
        $this->app['request']->merge([
            'error' => 'access_denied',
        ]);

        $this->expectException(CodeErrorException::class);
        $this->expectExceptionMessage('access_denied');

        $router = new CustomSSORouterWithoutScope();
        $router->callbackAction();
    }
}
