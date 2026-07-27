import { Controller } from '@hotwired/stimulus';

const collections = {};

export default class extends Controller {
    static values = {
        option: {
            type: String,
            default: 'Add …',
        },
        prompt: {
            type: String,
            default: 'Please enter the a option:',
        },
    };

    initialize() {
        this.change = this.change.bind(this);
    }

    connect() {
        this.previousValue = this.element.value;
        this.parentId = this.element.closest('table').id;

        if (this.parentId) {
            collections[this.parentId] = collections[this.parentId] || [];
            collections[this.parentId].push(this.element);
        }

        this.option = document.createElement('option');
        this.option.innerText = this.optionValue;
        this.option.value = 'extendSelect';

        this.element.append(this.option);
        this.element.addEventListener('change', this.change);
    }

    disconnect() {
        this.option?.remove();
        this.element.removeEventListener('change', this.change);
    }

    change() {
        if (this.element.value !== 'extendSelect') {
            this.previousValue = this.element.value;
            return;
        }

        const name = prompt(this.promptValue);

        if (!name) {
            this.element.value = this.previousValue;
            this.element.dispatchEvent('change');
            return;
        }

        const option = document.createElement('option');
        option.innerText = name;
        option.value = name;

        if (this.parentId && collections[this.parentId]) {
            collections[this.parentId].forEach((s) => {
                s.insertBefore(option.cloneNode(true), s.lastElementChild);
            });
        } else {
            this.element.insertBefore(option.cloneNode(true), this.element.lastElementChild);
        }

        this.element.value = name;
        this.previousValue = name;

        this.element.dispatchEvent('change');
    }
}
