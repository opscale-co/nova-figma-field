<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Dashboards\Main;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Override;
use Workbench\App\Models\User;
use Workbench\App\Nova\Design;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    #[Override]
    public function boot(): void
    {
        parent::boot();

        Route::get('/login', static fn () => redirect('/nova/login'))->name('login');
    }

    public function tools(): array
    {
        return [];
    }

    protected function resources(): void
    {
        Nova::resources([
            Design::class,
        ]);
    }

    protected function gate(): void
    {
        Gate::define('viewNova', static fn (User $user): bool => true);
    }

    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes(default: true)
            ->withPasswordResetRoutes()
            ->register();
    }

    protected function dashboards(): array
    {
        return [
            new Main,
        ];
    }
}
