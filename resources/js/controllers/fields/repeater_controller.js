import {Controller} from "stimulus"

import dragula  from "dragula";

export default class extends Controller {
    connect() {
        let items = this.element.querySelector('.repeater-item');

        let drake = dragula({
            moves: function (el, container, handle) {
                return handle.classList.contains('card-handle');
            }
        });

    }

}