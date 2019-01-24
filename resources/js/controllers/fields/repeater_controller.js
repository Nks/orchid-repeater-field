import {Controller} from "stimulus"

import dragula from "dragula";
let sqrl = require('squirrelly');

export default class extends Controller {
    /**
     * @type {string[]}
     */
    static targets = [
        "template"
    ];

    connect() {
        sqrl.autoEscaping(false);
        this.fetchFields();
        this.initActions();
    }

    fetchFields() {
        let field_name = this.data.get('name'),
            container_id = this.data.get('id'),
            container = this.element.querySelector(`#${container_id}`),
            elements_count = this.data.get('count'),
            template = sqrl.Compile(this.templateTarget.innerHTML),
            value = JSON.parse(this.data.get('value'));

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content'),
            },
        });

        axios.post(this.data.get('url'), {
            value: value,
            repeater_name: field_name
        }).then((r) => {
            r.data.results.forEach((v, k) => {
                container.innerHTML += template({content: v});
            });
        });
    }

    initActions() {
        let container_id = this.data.get('id'),
            container = this.element.querySelector(`#${container_id}`);

        $('.card-hcopy').on('click', function () {
            let tekparent = $(this).parents('.repeater-item'),
                count = $(container).children('.repeater-item').length,
                $clone = tekparent.clone(true);

            $clone.find("input, textarea").each(function (index, element) {
                let tekname = $(element).attr('name');
                tekname = tekname.replace("[" + tekid + "]", "[" + count + "]");
                $(element).attr('name', tekname);
            });
            $clone.appendTo(container);
        });

        $('.card-hdelete').on('click', function () {
            $(this).parents('.repeater-item').remove();
        });


        dragula([container], {
            moves: function (el, container, handle) {
                return handle.classList.contains('card-handle');
            }
        });
    }

}