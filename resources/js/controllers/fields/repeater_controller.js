import {Controller} from 'stimulus';

import dragula from 'dragula';

import axios from 'axios';

const sqrl = require('squirrelly');

export default class extends Controller {
    /**
     * @type {string[]}
     */
    static targets = [
        'blocks',
        'content',
        'repeaterBlockCount',
        'addBlockButton',
        'repeaterField',
    ];

    template;

    options = {
        required: false,
        min: null,
        max: null,
    };

    drake = null;

    connect() {
        if (document.documentElement.hasAttribute('data-turbolinks-preview') || document.body.classList.contains('gu-unselectable')) {
            return;
        }

        this.options = Object.assign(this.options, JSON.parse(this.data.get('options')));
        this.prepareTemplate();
        this.fetchFields();
        this.initDragDrop();
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

        const self = this;
        const fieldName = this.repeaterFieldTarget.name;
        const values = JSON.parse(this.data.get('value'));

        this.contentTarget.classList.add('loading');

        axios.post(this.data.get('url'), {
            values: values,
            repeater_name: fieldName,
            layout: this.data.get('layout'),
        }).then((r) => {
            if (!this.template && r.data.template) {
                const element = document.createElement('template');

                element.innerHTML = r.data.template.trim();

                const template = element.content.firstChild;

                this.template = sqrl.Compile(template.innerHTML);
            }

            if (!this.template) {
                window.platform.alert(`Error fetching repeater field template for ${this.options.title} (${this.options.name}).`, 'danger');

                return;
            }

            if (r.data.fields) {
                r.data.fields.forEach((content, index) => {
                    if (this.options.max === null || index < this.options.max) {
                        this.blocksTarget.insertAdjacentHTML('beforeend', this.template({
                            name: this.blocksTarget.dataset.containerKey,
                            content: content,
                            block_key: index,
                            block_count: this.options.title + ' ' + (index + 1),
                        }));
                    }
                });
            }

            this.contentTarget.classList.remove('loading');

            this.initMinRequiredBlock();

            this.checkEmpty();
        });
    }

    initMinRequiredBlock() {
        //   Exit when required or min aren't set
        if (this.options.required !== true && !this.options.min) {
            return;
        }

        const blocksCount = this.blocksTarget.querySelectorAll(':scope > .repeater-item').length;

        if (!blocksCount && this.options.required === true && this.options.min === null) {
            this.options.min = 1;
        }

        if (this.options.min !== null && this.options.min > blocksCount) {
            const click = new CustomEvent('click', {
                detail: {
                    blocksNum: this.options.min - blocksCount,
                },
            });

            this.addBlockButtonTarget.dispatchEvent(click);
        }
    }

    /**
     * Initialize drag n' drop ability
     */
    initDragDrop() {
        const self = this;

        this.drake = dragula([this.blocksTarget], {
            moves: function (el, container, handle) {
                const isCorrectHandle = (handle.dataset.parentContainerKey === self.blocksTarget.dataset.containerKey);

                return handle.classList.contains('card-handle') && isCorrectHandle;
            },
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

    addNewBlock() {
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

        const blocksCount = this.blocksTarget.querySelectorAll(':scope > .repeater-item').length;
        const num = event.detail.blocksNum || 1;

        if (this.options.max && blocksCount >= this.options.max) {
            alert('Maximum number of blocks reached');
            return;
        }

        axios.post(this.data.get('url'), {
            layout: this.data.get('layout'),
            repeater_name: this.repeaterFieldTarget.name,
            blocks: blocksCount,
            num: num,
        }).then((r) => {
            if (r.data.fields) {
                r.data.fields.forEach((content, index) => {
                    const compiledTemplate = this.template({
                        name: this.blocksTarget.dataset.containerKey,
                        content: content,
                        block_key: index,
                        block_count: this.options.title + ' ' + (index + 1),
                    });

                    if (currentBlock != null) {
                        currentBlock.insertAdjacentHTML('afterend', compiledTemplate);
                    } else {
                        this.blocksTarget.insertAdjacentHTML('beforeend', compiledTemplate);
                    }
                });
            }

            this.sort();
            this.checkEmpty();
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
        const self = this;
        // repeater_fieldName = this.repeaterFieldTarget.name,
        //  We must fetch only first level of the repeater fields
        const blocks = this.blocksTarget.querySelectorAll(':scope > .repeater-item');

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

                const resultName = field.closest('.repeaters_container').dataset.containerKey + '[' + field.closest('.repeater-item').dataset.sort + ']' + originalName;

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
