<?php

declare(strict_types=1);

namespace Opscale\Fields\Tests;

use Laravel\Dusk\Browser;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Override;

abstract class DuskTestCase extends BaseTestCase
{
    use WithWorkbench;

    protected static $baseServePort = 8089;

    final protected function loginToNova(Browser $browser): Browser
    {
        $browser->visit('/nova');

        if ($browser->element('input[name="email"]') !== null) {
            $browser->type('email', 'admin@laravel.com')
                ->type('password', 'password')
                ->press('Log In')
                ->waitForLocation('/nova/dashboards/main', 10);
        }

        return $browser;
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('app.key', 'base64:rVTYdg37aaGuFCOnLwfA+TdLt8IptDnHnoLspuGHoxc=');
    }
}
