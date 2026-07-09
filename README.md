## Support us

At Opscale, we’re passionate about contributing to the open-source community by providing solutions that help businesses scale efficiently. If you’ve found our tools helpful, here are a few ways you can show your support:

⭐ **Star this repository** to help others discover our work and be part of our growing community. Every star makes a difference!

💬 **Share your experience** by leaving a review on [Trustpilot](https://www.trustpilot.com/review/opscale.co) or sharing your thoughts on social media. Your feedback helps us improve and grow!

📧 **Send us feedback** on what we can improve at [feedback@opscale.co](mailto:feedback@opscale.co). We value your input to make our tools even better for everyone.

🙏 **Get involved** by actively contributing to our open-source repositories. Your participation benefits the entire community and helps push the boundaries of what’s possible.

💼 **Hire us** if you need custom dashboards, admin panels, internal tools or MVPs tailored to your business. With our expertise, we can help you systematize operations or enhance your existing product. Contact us at hire@opscale.co to discuss your project needs.

Thanks for helping Opscale continue to scale! 🚀



## Description

Design files usually live in Figma and rarely make it into the apps they describe. This package closes that gap: upload a `.fig` export or a Figma REST API JSON from any Nova resource and the design renders right on the record — read-only, pixel-faithful, no Figma account or API token required at view time. Rendering happens fully in the browser via [`@grida/refig`](https://www.npmjs.com/package/@grida/refig) (Skia over WebAssembly). Compatible with Nova 5.

## Installation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opscale-co/nova-figma-field.svg?style=flat-square)](https://packagist.org/packages/opscale-co/nova-figma-field)

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require opscale-co/nova-figma-field
```

The package will auto-register its service provider.

Back your field with a `string` column on the owning model — it stores the disk path of the uploaded Figma file. The file itself lives on the Laravel disk of your choice (typically `public`).

```php
Schema::create('designs', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('figma_path')->nullable();
    $table->timestamps();
});
```

Make sure the disk is publicly readable (for the default `public` disk: `php artisan storage:link`).

## Usage

Add the `Figma` field to any Nova Resource that owns a Figma attribute:

```php
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\Fields\Figma;

class Design extends Resource
{
    public static $model = \App\Models\Design::class;

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required', 'max:255'),

            Figma::make('Design', 'figma_path', 'public')
                ->prunable()
                ->deletable()
                ->height(600)
                ->scale(2)
                ->framePicker(),
        ];
    }
}
```

On **Create / Update** the field renders Nova’s native File dropzone — pick a `.fig` (Figma → *File → Save local copy*) or a REST API JSON export (`GET /v1/files/:key`), it is uploaded to the configured disk and the path is persisted. On **Detail / Lens** the field fetches the file from the disk and renders the selected frame as a crisp raster image, with a frame picker when the file has more than one top-level frame. It is intentionally **hidden from Index** and intentionally **not editable** — this is a viewer, not a design tool.

### Field behavior

| View | Behavior |
|---|---|
| Create / Update | Nova’s native File field dropzone (`.fig`, `.json`). Inherited storage, deletion, and `->prunable()` semantics. |
| Detail / Lens | Read-only render of the design via `@grida/refig` (Skia/WASM), with a page/frame picker. |
| Index | Hidden — a full design inside a table cell adds no decision-making value. |

### Modifiers

| Method | Purpose |
|---|---|
| `->height(int $pixels)` | Max height of the render viewport in pixels (default `600`; taller frames scroll). |
| `->scale(float $factor)` | Raster scale factor for the render (default `2` for crisp displays). |
| `->framePicker(bool $enabled = true)` | Show the page/frame dropdown when the file has multiple top-level frames. |
| `->wasmUrl(string $url)` | Override where the Skia WASM binary (~17 MB, cached immutably) is fetched from — e.g. a CDN. Defaults to a route served by this package. |
| `->prunable()` / `->deletable()` | Inherited from `Laravel\Nova\Fields\File` — remove the stored file when the record is deleted or the field is cleared. |

### Under the hood

| Layer | Dependency |
|---|---|
| Upload / storage | `Laravel\Nova\Fields\File` — disk-backed upload, deletion, prunable. |
| Renderer | [`@grida/refig`](https://grida.co/docs/packages/@grida/refig) `FigmaRenderer` over [`@grida/canvas-wasm`](https://www.npmjs.com/package/@grida/canvas-wasm) (Skia). |

The PHP field class is a thin subclass of Nova’s `File`; the Vue `form` and `index` slots alias `form-file-field` and `index-file-field` verbatim, so there is no custom upload UI to maintain. Only the `detail` slot is custom — it reads `field.previewUrl`, fetches the bytes, parses them with `FigmaDocument` (`.fig` binary or REST JSON), and renders the chosen frame to a PNG.

Notes:

- The WASM renderer binary is served by this package at `/nova-vendor/nova-figma-field/grida_canvas_wasm.wasm` with immutable cache headers; it downloads once per browser.
- Figma’s default fonts (Inter, Noto Sans variants) are loaded from Google Fonts’ CDN by the renderer on first use. Custom/brand typefaces fall back to the closest default — see refig’s bring-your-own-font docs if you need exact fidelity.
- Image fills are embedded in `.fig` exports and render as designed. REST JSON exports reference image fills by hash without the bytes, so those render without bitmaps.

## Testing

```bash

npm run test

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/opscale-co/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email development@opscale.co instead of using the issue tracker.

## Credits

- [Opscale](https://github.com/opscale-co)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
