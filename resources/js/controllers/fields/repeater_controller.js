import {Controller} from "stimulus"


export default class extends Controller {
    connect() {
        console.log(this.element);
        let blocks = this.element.querySelector('.group');

        console.log(blocks);
    }

}