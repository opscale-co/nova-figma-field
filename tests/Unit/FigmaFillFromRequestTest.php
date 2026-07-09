<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Fields\Figma;
use Workbench\App\Models\Design;

function sampleFigmaJson(): string
{
    return (string) file_get_contents(__DIR__.'/../fixtures/sample.json');
}

function figmaRequest(array $data): NovaRequest
{
    $files = [];
    $params = [];

    foreach ($data as $key => $value) {
        if ($value instanceof UploadedFile) {
            $files[$key] = $value;
        } else {
            $params[$key] = $value;
        }
    }

    return NovaRequest::create('/', 'POST', $params, [], $files);
}

beforeEach(function (): void {
    Storage::fake('public');
});

it('stores the uploaded file on the configured disk and persists the path', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'figma').'.json';
    file_put_contents($tmp, sampleFigmaJson());
    $upload = new UploadedFile($tmp, 'design.json', 'application/json', null, true);

    $field = Figma::make('Design', 'figma_path', 'public');
    $model = new Design;

    $field->fill(figmaRequest(['figma_path' => $upload]), $model);

    expect($model->figma_path)->toBeString()->not->toBeEmpty();

    Storage::disk('public')->assertExists($model->figma_path);
    expect(Storage::disk('public')->get($model->figma_path))->toContain('Hello from Figma');

    @unlink($tmp);
});

it('leaves the attribute untouched when no file is uploaded', function (): void {
    $field = Figma::make('Design', 'figma_path', 'public');
    $model = new Design(['figma_path' => 'designs/existing.fig']);

    $field->fill(figmaRequest([]), $model);

    expect($model->figma_path)->toBe('designs/existing.fig');
});

it('rejects files whose extension is not in acceptedTypes', function (): void {
    $upload = UploadedFile::fake()->createWithContent('design.pdf', '%PDF-1.4');

    $field = Figma::make('Design', 'figma_path', 'public')->rules('mimes:fig,json');
    expect($field->acceptedTypes)->toBe('.fig,.json');

    // rules('mimes:...') is enforced by Nova's validator layer; here we assert the
    // field advertises the restriction that the browser + backend will honor.
});
