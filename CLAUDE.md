# nova-figma-field

Laravel Nova 5 field that stores a Figma file (`.fig` or REST API JSON) like a regular Nova `File` field and renders it read-only on the detail view via `@grida/refig` (Skia/WASM, fully client-side).

## Architecture

- `src/Figma.php` — thin subclass of `Laravel\Nova\Fields\File`; adds viewer meta (`height`, `scale`, `framePicker`, `wasmUrl`).
- `src/FieldServiceProvider.php` — registers the compiled JS/CSS with Nova and a route serving `dist/wasm/grida_canvas_wasm.wasm`.
- `resources/js/components/FigmaViewer.vue` — fetches the stored file, parses it with `FigmaDocument`, lists top-level frames (via refig's internal `_resolve()` — version pinned), renders the selected frame to PNG.
- `resources/js/shims/` — webpack aliases: `module.js` fakes node's `createRequire` for refig's bundle; `canvas-wasm.js` injects `locateFile` so Emscripten fetches the WASM from this package's route instead of Nova's script URL.
- Form/Index slots delegate verbatim to Nova's `form-file-field` / `index-file-field` (see the comment in `FormField.vue` about NOT using the FormField mixin).

## Commands

- `composer serve` — build + run the Nova workbench at http://127.0.0.1:8000/nova (admin@laravel.com / password).
- `npm run production` — compile `dist/` (also copies the WASM binary).
- `npm run test` / `composer test` — Unit + Feature (Pest).
- `npm run test:web` — Dusk browser tests (builds + syncs the Dusk skeleton first).
- `npm run check` — duster fix + rector + phpstan + tests.
