import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        label: String,
        suffix: {
            type: String,
            default: '',
        },
    };

    static targets = ['checkbox'];

    connect() {
        if (!this.checkboxTarget) {
            return;
        }

        const fields = JSON.parse(this.checkboxTarget.getAttribute(`data-${this.identifier}-fields`));

        let injectError = false;

        fields.forEach((name) => {
            const el = document.getElementById(`ctrl_${name}${this.suffixValue}`);

            if (!el) {
                return;
            }

            const parent = this.#getParent(el);

            if (!parent) {
                injectError = true;
                return;
            }

            parent.classList.add('inherit');

            const container = document.createElement('div');
            const check = this.checkboxTarget.querySelector(`input[value=${name}]`);
            const label = this.checkboxTarget.querySelector(`label[for=${check.get('id')}]`);

            container.classList.add('inherit-default');
            label.innerHTML = this.labelValue;

            container.append(check, label);
            parent.prepend(container);

            // wait for e.g. tinyMCE to be loaded
            setTimeout(() => {
                this.#setState(el, !check.checked);
            }, 100);
        });

        if (!injectError) {
            this.checkboxTarget.parentNode.style.display = 'none';

            if (this.checkboxTarget.parentNode.closest('fieldset').querySelectorAll('.widget').length === 1) {
                this.checkboxTarget.parentNode.closest('fieldset').style.display = 'none';
            }
        }
    }

    toggleField(event) {
        const name = event.target.get('value');
        const el = document.getElementById(`ctrl_${name}${this.suffixValue}`);

        if (el) {
            this.#setState(el, !event.target.checked);
        }
    }

    #setState(el, visible) {
        const parent = this.#getParent(el);

        if (parent) {
            this.#toggleVisibility(parent.querySelector('label > span.invisible'), visible);
            this.#toggleVisibility(parent.querySelector('label > span.mandatory'), visible);
        }

        if (el.matches('.tl_checkbox_single_container')) {
            const input = el.querySelector('span > input.tl_checkbox');
            input.disabled = !visible;
            input.toggleAttribute('required', visible);
            return;
        }

        el.toggleAttribute('required', visible);

        // textarea with TinyMCE is already invisible, tinyMCE element will be hidden through nextElementSibling
        if (!el.matches('[data-controller*="contao--tinymce"]')) {
            this.#toggleVisibility(el, visible);
        }

        let next = el.nextElementSibling;

        while (next) {
            if (next.tagName !== 'SCRIPT') {
                this.#toggleVisibility(next, visible);
            }
            next = next.nextElementSibling;
        }
    }

    #getParent(el) {
        const parent = el.closest('div.widget').querySelector('h3');

        if (!parent && el.matches('.tl_checkbox_single_container')) {
            return el;
        }

        return parent;
    }

    #toggleVisibility(el, visible) {
        if (el) {
            el.style.display = visible ? '' : 'none';
        }
    }
}
