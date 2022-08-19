import FieldsRepeater from './controllers/fields/repeater_controller';

if (typeof window.application !== 'undefined') {
    window.application.register('fields--repeater', FieldsRepeater);
}
