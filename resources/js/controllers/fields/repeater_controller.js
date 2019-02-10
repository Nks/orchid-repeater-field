import {Controller} from "stimulus"

import dragula from "dragula";

let sqrl = require('squirrelly');

export default class extends Controller {
    /**
     * @type {string[]}
     */
    static targets = [
        "blocks",
        "content",
        "repeaterBlockCount",
        "addBlockButton",
        "repeaterField"
    ];

    template;
    options = {
        required: false,
        min: null,
        max: null
    };

    drake = null;

    connect() {
        //We not needed work with this on preview or when we dragging elements
        if (document.documentElement.hasAttribute("data-turbolinks-preview") || document.body.classList.contains('gu-unselectable')) {
            return;
        }

        this.options = Object.assign(this.options, JSON.parse(this.data.get('options')));
        this.prepareTemplate()
            .fetchFields()
            .initDragDrop();
    }

    prepareTemplate() {
        const templateElement = document.getElementById(this.data.get('template'));
        sqrl.autoEscaping(false);

        if (templateElement) {
            this.template = sqrl.Compile(templateElement.innerHTML);
        }

        return this;
    }

    /**
     * Retrieving fields from the backend if they are exists
     */
    fetchFields() {
        if (this.drake && this.drake.dragging === true) {
            return;
        }

        let self = this,
            field_name = this.repeaterFieldTarget.name,
            values = JSON.parse(this.data.get('value'));

        this.contentTarget.classList.add('loading');

        axios.post(this.data.get('url'), {
            values: values,
            repeater_name: field_name
        }).then((r) => {
            //if we haven't template yet (nested repeater) we can get it from the widget directly
            if (!this.template && r.data.results.template) {
                let element = document.createElement('template');

                element.innerHTML = r.data.results.template.trim();

                let template = element.content.firstChild;

                this.template = sqrl.Compile(template.innerHTML);
            }

            if (!this.template) {
                window.platform.alert(`Error fetching repeater field template for ${this.options.title} (${this.options.name}).`, 'danger');

                return;
            }

            if (r.data.results.fields) {
                r.data.results.fields.forEach((content, index) => {
                    if (self.options.max === null || index < self.options.max) {
                        self.blocksTarget.insertAdjacentHTML('beforeend', self.template({
                            name: self.blocksTarget.dataset.containerKey,
                            content: content,
                            block_key: index,
                            block_count: self.options.title + ' ' + (index + 1),
                        }));
                    }
                });
            }

            this.contentTarget.classList.remove('loading');

            self.initMinRequiredBlock();

            this.checkEmpty();
        });

        return this;
    }

    initMinRequiredBlock() {
        //Exit when required or min aren't set
        if (this.options.required !== true && !this.options.min) {
            return;
        }

        let blocksCount = this.blocksTarget.querySelectorAll(':scope > .repeater-item').length;

        if (!blocksCount && this.options.required === true && this.options.min === null) {
            this.options.min = 1;
        }

        if (this.options.min !== null && this.options.min > blocksCount) {
            const click = new CustomEvent('click', {
                detail: {
                    blocksNum: this.options.min - blocksCount,
                }
            });

            this.addBlockButtonTarget.dispatchEvent(click);
        }

        return this;
    }

    /**
     * Initialize drag n' drop ability
     */
    initDragDrop() {
        let self = this;

        this.drake = dragula([this.blocksTarget], {
            moves: function (el, container, handle) {
                let isCorrectHandle = (handle.dataset.parentContainerKey === self.blocksTarget.dataset.containerKey);

                return handle.classList.contains('card-handle') && isCorrectHandle;
            }
        }).on('drop', () => {
            this.sort();
        });

        return this;
    }

    /**
     * Check if blocks empty
     */
    checkEmpty() {
        this.contentTarget.classList.toggle('empty', this.blocksTarget.querySelectorAll(':scope > .repeater-item').length === 0);

        return this;
    }

    addNewBlock(event) {
        this.addBlock();

        return this;
    }

    addBlockAfter(event) {
        const currentBlock = event.currentTarget.closest('.repeater-item');
        this.addBlock(currentBlock);

        return this;
    }

    /**
     * Adding new blocks based on number of blocks which we have right now
     */
    addBlock(currentBlock) {
        let self = this;

        if (!this.template) {
            return;
        }

        let blocksCount = this.blocksTarget.querySelectorAll(':scope > .repeater-item').length,
            num = event.detail.blocksNum || 1;

        if (this.options.max && blocksCount >= this.options.max) {
            alert(`Maximum number of blocks reached`);
            return;
        }

        axios.post(this.data.get('url'), {
            repeater_name: this.repeaterFieldTarget.name,
            blocks: blocksCount,
            num: num
        }).then((r) => {
            let key = blocksCount;
            if (r.data.results.fields) {
                r.data.results.fields.forEach((content, index) => {
                    let compiledTemplate = this.template({
                        name: this.blocksTarget.dataset.containerKey,
                        content: content,
                        block_key: index,
                        block_count: self.options.title + ' ' + (index + 1),
                    });

                    console.log(self.options.title + ' ' + (index + 1));

                    if (currentBlock != null) {
                        currentBlock.insertAdjacentHTML('afterend', compiledTemplate);
                    } else {
                        this.blocksTarget.insertAdjacentHTML('beforeend', compiledTemplate);
                    }

                    key++;
                });
            }

            this.sort()
                .checkEmpty();
        });

        return this;
    }

    /**
     * Delete specified block based on event
     *
     * @param event
     */
    deleteBlock(event) {
        let blocksCount = this.blocksTarget.querySelectorAll(':scope > .repeater-item').length;

        if (this.options.min && blocksCount <= this.options.min) {
            alert(`Minimum number of blocks reached`);
            return;
        }

        event.currentTarget.closest('.repeater-item').remove();

        this.sort()
            .checkEmpty();

        return this;
    }

    /**
     * Sorting nested fields
     */
    sort() {
        let self = this,
            repeater_field_name = this.repeaterFieldTarget.name,
            //We must fetch only first level of the repeater fields
            blocks = this.blocksTarget.querySelectorAll(':scope > .repeater-item');

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

                const resultName = repeater_field_name + '[' + currentKey + ']' + originalName;

                field.setAttribute('name', resultName);
            })
        });

        if (this.hasRepeaterBlockCountTarget) {
            this.repeaterBlockCountTargets.forEach((content, index) => {
                content.innerHTML = self.options.title + ' ' + (index + 1);
            })
        }

        return this;
    }

}