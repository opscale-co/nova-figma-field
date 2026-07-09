import DetailField from './components/DetailField.vue'
import FormField from './components/FormField.vue'
import IndexField from './components/IndexField.vue'

Nova.booting((app) => {
    app.component('index-nova-figma-field', IndexField)
    app.component('form-nova-figma-field', FormField)
    app.component('detail-nova-figma-field', DetailField)
})
