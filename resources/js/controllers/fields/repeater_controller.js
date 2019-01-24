import {Controller} from "stimulus"

import dragula from "dragula";

let sqrl = require('squirrelly');

export default class extends Controller {
    /**
     * @type {string[]}
     */
    static targets = [
        "template",
        "repeaterContainer"
    ];

    template;

    connect() {
        sqrl.autoEscaping(false);
        this.template = sqrl.Compile(this.templateTarget.innerHTML);

        this.fetchFields();
        this.initDragDrop();
    }

    /**
     * Retrieving fields from the backend if they are exists
     */
    fetchFields() {
        let field_name = this.data.get('name'),
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
                this.repeaterContainerTarget.innerHTML += this.template({
                    content: v,
                    block_key: k
                });
            });
        });
    }

    /**
     * Initialize drag n' drop ability
     */
    initDragDrop() {
        let self = this;

        dragula([this.repeaterContainerTarget], {
            moves: function (el, container, handle) {
                return handle.classList.contains('card-handle');
            }
        }).on('drop', function () {
            self.sort();
        });
    }

    /**
     * Adding new blocks based on number of blocks which we have right now
     */
    addBlock() {
        const blocksCount = this.repeaterContainerTarget.querySelectorAll('.repeater-item').length;

        axios.post(this.data.get('url'), {
            repeater_name: this.data.get('name'),
            blocks: blocksCount
        }).then((r) => {
            let key = blocksCount;
            r.data.results.forEach((v, k) => {
                this.repeaterContainerTarget.innerHTML += this.template({
                    content: v,
                    block_key: key
                });

                key++;
            });

            this.sort();
        });
    }

    /**
     * Delete specified block based on event
     *
     * @param event
     */
    deleteBlock(event) {
        $(event.currentTarget).parents('.repeater-item').remove();
    }

    /**
     * Sorting nested fields
     *
     * TODO: Do we need create a labels for this based on new order?
     */
    sort() {
        let repeater_field_name = this.data.get('name'),
            blocks = this.repeaterContainerTarget.querySelectorAll('.repeater-item');

        blocks.forEach((block, currentKey) => {
            block.dataset.sort = currentKey;
            const fields = block.querySelectorAll('[data-repeater-name-key]');

            if (!fields.length) {
                return;
            }

            fields.forEach((field) => {
                let originalName = '[' + field.dataset.repeaterNameKey + ']';

                if (originalName.indexOf('[]')) {
                    originalName.replace('[]', '');
                }

                field.setAttribute('name', repeater_field_name + '[' + currentKey + ']' + originalName);
            })
        })

    }

}