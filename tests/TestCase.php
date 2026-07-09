<?php

declare(strict_types=1);

namespace Opscale\Fields\Tests;

use Inertia\ServiceProvider;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Nova\NovaCoreServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Opscale\Fields\FieldServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;

abstract class TestCase extends BaseTestCase
{
    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            ServiceProvider::class,
            FortifyServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            FieldServiceProvider::class,
        ]);
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
