<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <div data-testid="figma-detail">
                <p v-if="error" class="text-sm text-red-500" role="alert">{{ error }}</p>

                <FigmaViewer
                    v-else-if="bytes"
                    :bytes="bytes"
                    :height="field.height"
                    :scale="field.scale"
                    :frame-picker="field.framePicker"
                    :wasm-url="field.wasmUrl"
                />

                <p v-else class="text-sm text-gray-500">{{ __('No Figma file provided.') }}</p>
            </div>
        </template>
    </PanelItem>
</template>

<script>
import FigmaViewer from './FigmaViewer.vue'

export default {
    components: { FigmaViewer },
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

    data: () => ({
        bytes: null,
        error: '',
    }),

    mounted() {
        this.load()
    },

    watch: {
        'field.previewUrl': 'load',
        'field.value': 'load',
    },

    methods: {
        async load() {
            this.error = ''
            this.bytes = null

            const url = this.sameOriginUrl(this.field.previewUrl)
            if (!url) return

            try {
                const response = await fetch(url, { credentials: 'same-origin' })
                if (!response.ok) throw new Error(`HTTP ${response.status}`)
                this.bytes = new Uint8Array(await response.arrayBuffer())
            } catch (err) {
                this.error = this.__('Could not load the Figma file: ') + err.message
            }
        },
        sameOriginUrl(raw) {
            if (!raw) return ''

            try {
                const parsed = new URL(raw, window.location.origin)
                const loopback = new Set(['localhost', '127.0.0.1', '::1', '0.0.0.0'])
                const sameOrigin = parsed.origin === window.location.origin
                const bothLoopback = loopback.has(parsed.hostname) && loopback.has(window.location.hostname)

                if (sameOrigin || bothLoopback) {
                    return parsed.pathname + parsed.search
                }
                return parsed.href
            } catch {
                return raw
            }
        },
    },
}
</script>
