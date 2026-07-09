/**
 * Browser stand-in for node's "module" builtin, aliased in webpack.mix.js.
 *
 * @grida/refig's bundle calls `createRequire(import.meta.url)` at module scope
 * and only ever require()s "util" (for TextDecoder/TextEncoder, both browser
 * globals) and "worker_threads" (inside a try/catch). Everything else throws,
 * matching what those code paths expect outside node.
 */
export function createRequire() {
    return function require(id) {
        if (id === 'util') {
            return { TextDecoder, TextEncoder }
        }

        throw new Error(`Cannot require "${id}" in the browser`)
    }
}

export default { createRequire }
