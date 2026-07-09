<template>
    <div data-testid="figma-form-field">
        <form-file-field
            :field="field"
            :errors="errors"
            :resource-name="resourceName"
            :resource-id="resourceId"
            :resource-relationship-name="resourceRelationshipName"
            :related-resource-name="relatedResourceName"
            :related-resource-id="relatedResourceId"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            :via-relationship="viaRelationship"
            :full-width-content="fullWidthContent"
            :show-help-text="showHelpText"
        />
    </div>
</template>

<script>
import { HandlesValidationErrors } from 'laravel-nova'

/**
 * We intentionally do NOT include the `FormField` mixin here.
 *
 * Nova's built-in File field registers its upload payload by assigning
 * `this.field.fill = (formData) => ...` inside its mounted() — that arrow
 * function captures the selected File object from closure. The `FormField`
 * mixin's own mounted() ALSO does `this.field.fill = this.fill`, where
 * `this.fill` is the generic `formData.append(attr, String(this.value))`.
 *
 * Because parent components mount AFTER their children in Vue's lifecycle,
 * adding the `FormField` mixin to this wrapper would overwrite the child's
 * upload-aware `field.fill` with the string-only default, and the Figma file
 * would never reach the backend.
 *
 * By skipping the mixin, the child's `field.fill` stays authoritative and
 * Nova's form submission picks up the uploaded file as expected.
 */
export default {
    mixins: [HandlesValidationErrors],

    props: [
        'resourceName',
        'resourceId',
        'resourceRelationshipName',
        'relatedResourceName',
        'relatedResourceId',
        'viaResource',
        'viaResourceId',
        'viaRelationship',
        'field',
        'fullWidthContent',
        'showHelpText',
    ],
}
</script>
