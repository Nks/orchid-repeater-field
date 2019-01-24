import FieldsRepeater from "./controllers/fields/repeater_controller"

//We can work with this only when we already have an application
if (typeof window.application !== 'undefined') {
    window.application.register('fields--repeater', FieldsRepeater)
}
