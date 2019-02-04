import {Controller} from "stimulus"

import dragula from "dragula";

let sqrl = require('squirrelly');

export default class extends Controller {
    /**
     * @type {string[]}
     */
    static targets = [
        "template",
        "blocks",
        "content",
        "repeaterBlockCount",
        "addBlockButton"
    ];

    template;
    options = {
        required: false,
        min: null,
        max: null
    };

    connect() {
        //We not needed work with this on preview
        if (document.documentElement.hasAttribute("data-turbolinks-preview")) {
            return;
        }

        this.options = Object.assign(this.options, JSON.parse(this.data.get('options')));

        sqrl.autoEscaping(false);
        this.template = sqrl.Compile(this.templateTarget.innerHTML);
        this.fetchFields();
        this.initDragDrop();
    }

    /**
     * Retrieving fields from the backend if they are exists
     */
    fetchFields() {
        let self = this,
            field_name = this.data.get('name'),
            value = JSON.parse(this.data.get('value'));

        this.contentTarget.classList.add('loading');

        axios.post(this.data.get('url'), {
            value: value,
            repeater_name: field_name
        }).then((r) => {
            r.data.results.forEach((v, k) => {
                if (self.options.max === null || k < self.options.max) {
                    let content = v.join('', v);
                    self.blocksTarget.insertAdjacentHTML('beforeend', self.template({
                        content: content,
                        block_key: k,
                        block_count: k + 1
                    }));
                }
            });

            this.contentTarget.classList.remove('loading');

            self.initMinRequiredBlock();

            this.checkEmpty();
        });
    }

    initMinRequiredBlock() {
        //Exit when required or min aren't set
        if (this.options.required !== true && !this.options.min) {
            return;
        }

        let blocksCount = this.blocksTarget.querySelectorAll('.repeater-item').length;

        if (!blocksCount && this.options.required === true && this.options.min === null) {
            this.options.min = 1;
        }

        if (this.options.min !== null && this.options.min > blocksCount) {
            const click = new CustomEvent('click', {
                detail: {
                    blocksNum: this.options.min - blocksCount
                }
            });

            this.addBlockButtonTarget.dispatchEvent(click);
        }
    }

    /**
     * Initialize drag n' drop ability
     */
    initDragDrop() {
        let self = this;

        dragula([this.blocksTarget], {
            moves: function (el, container, handle) {
                return handle.classList.contains('card-handle');
            }
        }).on('drop', function () {
            self.sort();
        });
    }

    /**
     * Check if blocks empty
     */
    checkEmpty() {
        this.contentTarget.classList.toggle('empty', this.blocksTarget.querySelectorAll('.repeater-item').length === 0);
    }

    addNewBlock(event) {
        this.addBlock();
    }

    addBlockAfter(event) {
        const currentBlock = event.currentTarget.closest('.repeater-item');
        this.addBlock(currentBlock)
    }

    /**
     * Adding new blocks based on number of blocks which we have right now
     */
    addBlock(currentBlock) {
        let blocksCount = this.blocksTarget.querySelectorAll('.repeater-item').length,
            num = event.detail.blocksNum || 1;

        if (this.options.max && blocksCount >= this.options.max) {
            alert(`Maximum number of blocks reached`);
            return;
        }

        axios.post(this.data.get('url'), {
            repeater_name: this.data.get('name'),
            blocks: blocksCount,
            num: num
        }).then((r) => {
            let key = blocksCount;
            r.data.results.forEach((v, k) => {
                let content = v.join('', v),
                    compiledTemplate = this.template({
                        content: content,
                        block_key: key,
                        block_count: key + 1
                    });

                if (currentBlock != null) {
                    currentBlock.insertAdjacentHTML('afterend', compiledTemplate);
                } else {
                    this.blocksTarget.insertAdjacentHTML('beforeend', compiledTemplate);
                }

                key++;
            });

            this.sort();

            this.checkEmpty();
        });
    }

    /**
     * Delete specified block based on event
     *
     * @param event
     */
    deleteBlock(event) {
        let blocksCount = this.blocksTarget.querySelectorAll('.repeater-item').length;

        if (this.options.min && blocksCount <= this.options.min) {
            alert(`Minimum number of blocks reached`);
            return;
        }

        event.currentTarget.closest('.repeater-item').remove();

        this.sort();

        this.checkEmpty();
    }

    /**
     * Sorting nested fields
     */
    sort() {
        let repeater_field_name = this.data.get('name'),
            blocks = this.blocksTarget.querySelectorAll('.repeater-item');

        blocks.forEach((block, currentKey) => {
            block.dataset.sort = currentKey;
            const fields = block.querySelectorAll('[data-repeater-name-key]');

            if (!fields.length) {
                return;
            }

            fields.forEach((field) => {
                let repeaterNameKey = field.dataset.repeaterNameKey,
                    originalName = '[' + repeaterNameKey.replace('.', '') + ']';

                if (repeaterNameKey.endsWith('.')) {
                    originalName += '[]';
                }

                field.setAttribute('name', repeater_field_name + '[' + currentKey + ']' + originalName);
            })
        });

        if (this.hasRepeaterBlockCountTarget) {
            this.repeaterBlockCountTargets.forEach((v, k) => {
                v.innerHTML = k + 1;
            })
        }
    }

    disconnect() {
        this.blocksTarget.innerHTML = '';
        this.template = null;
    }

}