<?php

namespace FMCSSOClient\Tests;

use FMCSSOClient\Facades\SSOClient;
use Illuminate\Http\RedirectResponse;

class OAuthStep1Test extends TestCase
{

    /** @test */
    public function client_has_correct_redirect_request()
    {
        /** @var RedirectResponse $redirect */
        $redirect = SSOClient::redirect();
        $location = $redirect->getTargetUrl();

        // Configured to skip state
        $this->assertStringNotContainsString('state', $location);

        $this->assertStringContainsString('client_id=', $location);
        $this->assertStringContainsString('redirect_uri=', $location);
    }

    /** @test */
    public function additional_parameters_can_be_added()
    {
        /** @var RedirectResponse $redirect */
        $redirect = SSOClient::with([
            'example'      => 'val',
            'some'         => 'other',
            'redirect_uri' => 'overridden',
        ])->redirect();
        $location = $redirect->getTargetUrl();

        $this->assertStringNotContainsString('state', $location);
        $this->assertStringContainsString('client_id=', $location);
        $this->assertStringContainsString('redirect_uri=overridden', $location);
        $this->assertStringContainsString('some=other', $location);
        $this->assertStringContainsString('example=val', $location);
    }
}
