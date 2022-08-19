import Sortable from 'sortablejs';
import axios from 'axios';
import * as Sqrl from 'squirrelly';
import ApplicationController
    from '~orchid/js/controllers/application_controller';

export default class extends ApplicationController {
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
        collapse: false,
    };

    sortableInstance = null;

    connect() {
        if (document.documentElement.hasAttribute('data-turbolinks-preview')
      || document.body.classList.contains('gu-unselectable')) {
            return;
        }

        this.options = Object.assign(
            this.options,
            JSON.parse(this.data.get('options')),
        );

        this.prepareTemplate();
        this.fetchFields();
        this.initDragDrop();
    }

    prepareTemplate() {
        const templateElement = document.getElementById(this.data.get('template'));

        if (templateElement) {
            const config = Sqrl.defaultConfig;
            config.autoEscape = false;
            this.template = Sqrl.compile(templateElement.innerHTML, config);
        }

        return this;
    }

    fetchFields() {
        const fieldName = this.repeaterFieldTarget.name;
        const repeaterData = this.getRepeaterData();
        const values = JSON.parse(this.data.get('value'));

        this.contentTarget.classList.add('loading');

        axios.post(this.data.get('url'), {
            values,
            repeater_name: fieldName,
            layout: this.data.get('layout'),
            repeater_data: repeaterData,
        }).then((response) => {
            if (!this.template && response.data.template) {
                const element = document.createElement('template');

                element.innerHTML = response.data.template.trim();

                const template = element.content.firstChild;

                const config = Sqrl.defaultConfig;
                config.autoEscape = false;

                this.template = Sqrl.compile(template.innerHTML, config);
            }

            if (!this.template) {
                this.alert(
                    'Unexpected error',
                    `Error fetching repeater field template for ${this.options.title} (${this.options.name}).`,
                    'danger',
                );

                return;
            }

            if (response.data.fields) {
                response.data.fields.forEach((content, index) => {
                    if (this.options.max === null || index < this.options.max) {
                        this.blocksTarget.insertAdjacentHTML('beforeend', this.template({
                            name: this.blocksTarget.dataset.containerKey,
                            content,
                            block_key: index,
                            block_count: `${this.options.title} ${index + 1}`,
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

        const blocksCount = this.blocksTarget.querySelectorAll(
            ':scope > .repeater-item',
        ).length;

        if (!blocksCount && this.options.required === true && this.options.min
      === null) {
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

    initDragDrop() {
        this.sortableInstance = Sortable.create(this.blocksTarget, {
            handle: '.card-handle',
            animation: 150,
            onEnd: () => {
                this.sort();
            },
        });

        return this;
    }

    checkEmpty() {
        this.contentTarget.classList.toggle(
            'empty',
            this.blocksTarget.querySelectorAll(':scope > .repeater-item').length
      === 0,
        );

        return this;
    }

    collapse(event) {
        const currentBlock = event.currentTarget.closest('.collapse-switch');

        currentBlock.querySelector('.transition').classList.toggle("collapse-action");

        currentBlock.parentElement.parentElement.parentElement.querySelector('.card-body').classList.toggle("collapse");
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

    addBlock(currentBlock) {
        if (!this.template) {
            this.alert('Error', 'No template is defined.', 'danger');
            return;
        }

        const blocksCount = this.blocksTarget.querySelectorAll(
            ':scope > .repeater-item',
        ).length;
        const num = event.detail.blocksNum || 1;
        const repeaterData = this.getRepeaterData();

        if (this.options.max && blocksCount >= this.options.max) {
            this.alert(
                this.data.get('error-title'),
                this.data.get('max-error-message'),
            );
            return;
        }

        axios.post(this.data.get('url'), {
            layout: this.data.get('layout'),
            repeater_name: this.repeaterFieldTarget.name,
            blocks: blocksCount,
            num,
            repeater_data: repeaterData,
        }).then((r) => {
            if (r.data.fields) {
                r.data.fields.forEach((content, index) => {
                    const compiledTemplate = this.template({
                        name: this.blocksTarget.dataset.containerKey,
                        content,
                        block_key: index,
                        block_count: `${this.options.title} ${index + 1}`,
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
    }

    deleteBlock(event) {
        if (!window.confirm(this.data.get('confirm-delete-message'))) {
            return;
        }

        const blocksCount = this.blocksTarget.querySelectorAll(
            ':scope > .repeater-item',
        ).length;

        if (this.options.min && blocksCount <= this.options.min) {
            this.alert(
                this.data.get('error-title'),
                this.data.get('min-error-message'),
            );

            return;
        }

        event.currentTarget.closest('.repeater-item').remove();

        this.sort().checkEmpty();
    }

    /**
   * Sorting nested fields
   */
    sort() {
        const self = this;

        const blocks = this.blocksTarget.querySelectorAll(
            ':scope > .repeater-item',
        );

        blocks.forEach((block, currentKey) => {
            block.dataset.sort = currentKey;
            const fields = block.querySelectorAll('[data-repeater-name-key]');

            if (!fields.length) {
                return;
            }

            fields.forEach((field) => {
                const { repeaterNameKey } = field.dataset;
                let originalName = `[${repeaterNameKey.replace('.', '')}]`;

                if (repeaterNameKey.endsWith('.')) {
                    originalName += '[]';
                }

                const resultName = `${field.closest(
                    '.repeaters_container',
                ).dataset.containerKey}[${
                    field.closest('.repeater-item').dataset.sort}]${originalName}`;

                field.setAttribute('name', resultName);
            });
        });

        if (this.hasRepeaterBlockCountTarget) {
            this.repeaterBlockCountTargets.forEach((content, index) => {
                content.innerHTML = `${self.options.title} ${index + 1}`;
            });
        }

        return this;
    }

    getRepeaterData() {
        return this.data.get('ajax-data')
            ? JSON.parse(this.data.get('ajax-data'))
            : null;
    }
}
