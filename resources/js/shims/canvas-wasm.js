/**
 * Wrapper around @grida/canvas-wasm, aliased in webpack.mix.js so that
 * @grida/refig's internal `createCanvas()` call resolves here.
 *
 * The Emscripten loader inside canvas-wasm locates `grida_canvas_wasm.wasm`
 * relative to the executing <script> — which, bundled into a Nova field,
 * points at Nova's script endpoint where no .wasm exists. This wrapper
 * injects a `locateFile` that returns the URL served by the field's
 * ServiceProvider (overridable per field via the `wasmUrl` meta, which the
 * viewer component mirrors into `window.__NOVA_FIGMA_FIELD_WASM_URL__`).
 */
import * as base from '@grida/canvas-wasm-original'

export const Canvas = base.Canvas
export const version = base.version
export const encodeButton = base.encodeButton
export const encodeModifiers = base.encodeModifiers
export default base.default

const DEFAULT_WASM_URL = '/nova-vendor/nova-figma-field/grida_canvas_wasm.wasm'

export function createCanvas(opts = {}) {
    return base.createCanvas({
        ...opts,
        locateFile:
            opts.locateFile ||
            (() => window.__NOVA_FIGMA_FIELD_WASM_URL__ || DEFAULT_WASM_URL),
    })
}
