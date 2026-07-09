let mix = require('laravel-mix')
let path = require('path')
let webpack = require('webpack')

mix
    .setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({ version: 3 })
    .css('resources/css/field.css', 'css')
    .copy('node_modules/@grida/canvas-wasm/dist/grida_canvas_wasm.wasm', 'dist/wasm')
    .webpackConfig({
        externals: {
            vue: 'Vue',
            'laravel-nova': 'LaravelNova',
            'laravel-nova-ui': 'LaravelNovaUi',
        },
        output: {
            uniqueName: 'opscale/nova-figma-field',
        },
        module: {
            rules: [
                // @grida ships .mjs ESM with extension-less internal imports.
                { test: /\.m?js$/, resolve: { fullySpecified: false } },
            ],
        },
        resolve: {
            symlinks: false,
            alias: {
                vue: path.resolve('./node_modules/vue'),
                // Wrap createCanvas so the Emscripten loader fetches the .wasm
                // from the URL served by the field's ServiceProvider instead of
                // resolving it relative to Nova's script tag.
                '@grida/canvas-wasm$': path.resolve('./resources/js/shims/canvas-wasm.js'),
                '@grida/canvas-wasm-original$': path.resolve('./node_modules/@grida/canvas-wasm/dist/index.mjs'),
                // refig's bundle calls createRequire() at module scope; give the
                // browser build a require() that satisfies its only real use ("util").
                module: path.resolve('./resources/js/shims/module.js'),
            },
            fallback: {
                fs: false,
                crypto: false,
                path: false,
                util: false,
                worker_threads: false,
            },
        },
        plugins: [
            // "node:" scheme requests bypass alias/fallback resolution; strip
            // the prefix so node:module hits the shim and node:fs/node:crypto
            // hit the fallbacks above.
            new webpack.NormalModuleReplacementPlugin(/^node:/, (resource) => {
                resource.request = resource.request.replace(/^node:/, '')
            }),
            new webpack.DefinePlugin({
                __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
            }),
        ],
    })
    .options({
        vue: {
            exposeFilename: true,
        },
    })
