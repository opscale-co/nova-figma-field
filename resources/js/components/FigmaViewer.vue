<template>
    <div class="nova-figma-wrapper" data-testid="figma-viewer">
        <div v-if="framePicker && frames.length > 1" class="nova-figma-toolbar" data-testid="figma-frame-picker">
            <select v-model="selectedFrame" class="nova-figma-select" @change="render">
                <option v-for="frame in frames" :key="frame.id" :value="frame.id">
                    {{ frame.label }}
                </option>
            </select>
        </div>

        <div class="nova-figma-canvas" :style="{ maxHeight: height + 'px' }" data-testid="figma-canvas">
            <p v-if="error" class="nova-figma-status text-sm text-red-500" role="alert" data-testid="figma-error">
                {{ error }}
            </p>
            <p v-else-if="rendering" class="nova-figma-status text-sm text-gray-500" data-testid="figma-loading">
                Rendering design…
            </p>
            <img
                v-else-if="imageUrl"
                :src="imageUrl"
                :style="{ width: imageWidth + 'px' }"
                class="nova-figma-image"
                alt="Figma design"
                data-testid="figma-image"
            />
        </div>
    </div>
</template>

<script>
import { FigmaDocument, FigmaRenderer } from '@grida/refig/browser'

export default {
    props: {
        bytes: { type: Uint8Array, required: true },
        height: { type: Number, default: 600 },
        scale: { type: Number, default: 2 },
        framePicker: { type: Boolean, default: true },
        wasmUrl: { type: String, default: '' },
    },

    data: () => ({
        frames: [],
        selectedFrame: '',
        imageUrl: '',
        imageWidth: 0,
        rendering: false,
        error: '',
    }),

    mounted() {
        if (this.wasmUrl) {
            window.__NOVA_FIGMA_FIELD_WASM_URL__ = this.wasmUrl
        }

        this.load()
    },

    beforeUnmount() {
        this.dispose()
    },

    watch: {
        bytes() {
            this.dispose()
            this.load()
        },
    },

    methods: {
        load() {
            this.error = ''
            this.frames = []
            this.selectedFrame = ''

            try {
                this.document = new FigmaDocument(this.parseInput(this.bytes))
                this.renderer = new FigmaRenderer(this.document)
                this.frames = this.listFrames(this.document)
            } catch (err) {
                this.error = 'Could not parse the Figma file: ' + (err?.message || err)
                return
            }

            if (this.frames.length === 0) {
                this.error = 'The Figma file has no renderable frames.'
                return
            }

            this.selectedFrame = this.frames[0].id
            this.render()
        },

        parseInput(bytes) {
            // A Figma REST API export is JSON; a .fig export is binary
            // (kiwi/ZIP). FigmaDocument accepts either, but expects JSON
            // pre-parsed into an object.
            let start = 0
            if (bytes[0] === 0xef && bytes[1] === 0xbb && bytes[2] === 0xbf) start = 3
            while (start < bytes.length && [0x20, 0x09, 0x0a, 0x0d].includes(bytes[start])) start++

            if (bytes[start] === 0x7b) {
                return JSON.parse(new TextDecoder().decode(bytes))
            }

            return bytes
        },

        listFrames(document) {
            // _resolve() is refig's cached document→IR step (the version is
            // pinned in package.json); the public API has no node listing yet.
            const resolved = document._resolve().document
            const frames = []
            const seen = new Set()

            for (const sceneId of resolved.scenes_ref || []) {
                const scene = resolved.nodes[sceneId]
                for (const nodeId of resolved.links?.[sceneId] || []) {
                    const node = resolved.nodes[nodeId]
                    // .fig documents can repeat a node across scene links.
                    if (!node || seen.has(nodeId)) continue
                    seen.add(nodeId)

                    const page = scene?.name || sceneId
                    frames.push({ id: nodeId, label: `${page} / ${node.name || nodeId}` })
                }
            }

            return frames
        },

        async render() {
            if (!this.renderer || !this.selectedFrame) return

            this.rendering = true
            this.error = ''

            try {
                const result = await this.renderer.render(this.selectedFrame, {
                    format: 'png',
                    scale: this.scale,
                })

                const decoded = await createImageBitmap(new Blob([result.data], { type: result.mimeType }))
                this.imageWidth = Math.round(decoded.width / this.scale)
                decoded.close()

                this.replaceImage(URL.createObjectURL(new Blob([result.data], { type: result.mimeType })))
            } catch (err) {
                this.error = 'Could not render the design: ' + (err?.message || err)
            } finally {
                this.rendering = false
            }
        },

        replaceImage(url) {
            if (this.imageUrl) {
                URL.revokeObjectURL(this.imageUrl)
            }
            this.imageUrl = url
        },

        dispose() {
            this.replaceImage('')
            this.renderer?.dispose()
            this.renderer = null
            this.document = null
        },
    },
}
</script>

<style scoped>
.nova-figma-wrapper {
    width: 100%;
    border: 1px solid rgba(var(--colors-gray-300));
    border-radius: 0.5rem;
    background: #fff;
    overflow: hidden;
}

.nova-figma-toolbar {
    display: flex;
    justify-content: flex-end;
    padding: 0.5rem;
    border-bottom: 1px solid rgba(var(--colors-gray-300));
    background: rgba(var(--colors-gray-100));
}

.nova-figma-select {
    max-width: 100%;
    padding: 0.25rem 0.5rem;
    border: 1px solid rgba(var(--colors-gray-300));
    border-radius: 0.375rem;
    background: #fff;
    font-size: 0.875rem;
}

.nova-figma-canvas {
    overflow: auto;
    background:
        repeating-conic-gradient(rgba(var(--colors-gray-100)) 0% 25%, #fff 0% 50%)
        50% / 24px 24px;
}

.nova-figma-image {
    display: block;
    max-width: none;
}

.nova-figma-status {
    padding: 1rem;
    background: #fff;
}
</style>
