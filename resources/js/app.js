import {definitionsFromContext} from 'stimulus/webpack-helpers';

//We can work with this only when we already have an application
if (typeof window.application !== 'undefined') {
    const context = require.context('./controllers', true, /\.js$/);
    window.application.load(definitionsFromContext(context));
}
