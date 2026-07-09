<?php

declare(strict_types=1);

use Laravel\Nova\Fields\File;
use Opscale\Fields\Figma;

it('uses the nova-figma-field Vue component', function (): void {
    $field = Figma::make('Design', 'figma_path');

    expect($field->component)->toBe('nova-figma-field');
});

it('is hidden from the index view', function (): void {
    $field = Figma::make('Design', 'figma_path');

    expect($field->showOnIndex)->toBeFalse();
});

it('inherits Laravel Nova File behavior', function (): void {
    $field = Figma::make('Design', 'figma_path');

    expect($field)->toBeInstanceOf(File::class);
});

it('advertises .fig and .json as accepted upload types', function (): void {
    $field = Figma::make('Design', 'figma_path');

    expect($field->acceptedTypes)->toBe('.fig,.json');
});

it('exposes viewer defaults via meta()', function (): void {
    $meta = Figma::make('Design', 'figma_path')->meta();

    expect($meta)
        ->toHaveKey('height', 600)
        ->toHaveKey('scale', 2)
        ->toHaveKey('framePicker', true)
        ->toHaveKey('wasmUrl', '/nova-vendor/nova-figma-field/grida_canvas_wasm.wasm');
});

it('overrides viewer defaults through chainable modifiers', function (): void {
    $meta = Figma::make('Design', 'figma_path')
        ->height(900)
        ->scale(1.5)
        ->framePicker(false)
        ->wasmUrl('https://cdn.example.com/grida_canvas_wasm.wasm')
        ->meta();

    expect($meta)
        ->toHaveKey('height', 900)
        ->toHaveKey('scale', 1.5)
        ->toHaveKey('framePicker', false)
        ->toHaveKey('wasmUrl', 'https://cdn.example.com/grida_canvas_wasm.wasm');
});

it('serializes the File-level meta keys expected by the form-file-field component', function (): void {
    $field = Figma::make('Design', 'figma_path');

    $json = $field->jsonSerialize();

    expect($json)
        ->toHaveKey('component', 'nova-figma-field')
        ->toHaveKey('attribute', 'figma_path')
        ->toHaveKey('acceptedTypes', '.fig,.json')
        ->toHaveKeys(['previewUrl', 'thumbnailUrl', 'downloadable', 'deletable']);
});

it('serves the wasm binary through the registered vendor route', function (): void {
    $response = $this->get('/nova-vendor/nova-figma-field/grida_canvas_wasm.wasm');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/wasm');
})->skip(
    fn (): bool => ! is_file(__DIR__.'/../../dist/wasm/grida_canvas_wasm.wasm'),
    'dist/wasm/grida_canvas_wasm.wasm not built yet — run `npm run production`.',
);
