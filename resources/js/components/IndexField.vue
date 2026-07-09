<template>
    <index-file-field :field="field" :resource-name="resourceName" />
</template>

<script>
export default {
    props: ['resourceName', 'field'],
}
</script>
