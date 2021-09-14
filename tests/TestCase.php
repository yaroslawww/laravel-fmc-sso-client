<?php

namespace FMCSSOClient\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \FMCSSOClient\ServiceProvider::class,
        ];
    }

    public function defineEnvironment($app)
    {
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('fmc-sso-client.client_id', 'test_client_id');
        $app['config']->set('fmc-sso-client.client_secret', 'test_client_secret');
        $app['config']->set('fmc-sso-client.redirect_url', 'test_redirect_url');
        $app['config']->set('fmc-sso-client.sso.useState', false);
    }
}
