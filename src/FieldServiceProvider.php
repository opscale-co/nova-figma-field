<?php

declare(strict_types=1);

namespace Opscale\Fields;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

final class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::serving(function (ServingNova $servingNova): void {
            Nova::script('nova-figma-field', __DIR__.'/../dist/js/field.js');
            Nova::style('nova-figma-field', __DIR__.'/../dist/css/field.css');
        });

        // The Skia/WASM renderer binary is fetched lazily by the detail view.
        Route::get('/nova-vendor/nova-figma-field/grida_canvas_wasm.wasm', static fn () => response()->file(
            __DIR__.'/../dist/wasm/grida_canvas_wasm.wasm',
            [
                'Content-Type' => 'application/wasm',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ],
        ));
    }

    #[\Override]
    public function register(): void
    {
        //
    }
}
