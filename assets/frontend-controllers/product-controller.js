import { Controller } from '@hotwired/stimulus';
import axios from '../scripts/_axios';
import { extractScript } from '../scripts/_dom';

export default class extends Controller {
    static values = {
        formId: String,
        attributes: Array,
    };

    static targets = ['form'];

    #abortController = null;

    connect() {
        if (!this.hasFormTarget) return;

        this.#connectAttributes();
    }

    disconnect() {
        this.#abortController?.abort();
    }

    update(e) {
        e.preventDefault();
        this.#abortController?.abort();

        this.element.setAttribute('aria-busy', 'true');
        this.formTarget.disabled = true;

        this.#ajaxSuccess(this.#submitForm());
    }

    async submit(event) {
        event.preventDefault();

        this.#abortController?.abort();

        this.element.setAttribute('aria-busy', 'true');
        this.formTarget.disabled = true;

        const data = new FormData(this.formTarget);

        if (event.submitter?.name && event.submitter?.value) {
            data.set(event.submitter.name, event.submitter.value);
        }

        await this.#submitForm(data);

        this.element.setAttribute('aria-busy', 'false');
        this.formTarget.disabled = false;

        this.dispatch('submit', {
            target: event.submitter,
            detail: { form: this.formTarget, submitter: event.submitter, data } /* , cancelable: true */,
        });

        if (event.submitter?.value) {
            this.dispatch(`submit.${event.submitter.value}`, {
                target: event.submitter,
                detail: { form: this.formTarget, submitter: event.submitter, data } /* , cancelable: true */,
            });
        }
    }

    #submitForm(formData) {
        this.#abortController = new AbortController();

        return axios.request({
            url: this.formTarget.getAttribute('action') || window.location.href,
            method: this.formTarget.getAttribute('method').toUpperCase(),
            data: formData || new FormData(this.formTarget),
            signal: this.#abortController.signal,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
        });
    }

    async #ajaxSuccess(axiosPromise) {
        const [txt, scripts] = extractScript((await axiosPromise).data);

        const div = document.createElement('div');
        div.innerHTML = txt;

        // Remove all error messages
        let skip = 0;
        const errors = div.getElementsByTagName('p');
        while (skip < errors.length) {
            if (errors[skip].className.search(/(^| )error( |$)/) !== -1) {
                errors[skip].parentNode.removeChild(errors[skip]);
            } else {
                skip += 1;
            }
        }

        this.element.replaceWith(...div.childNodes);

        if (scripts) {
            const script = document.createElement('script');
            script.text = scripts;
            document.head.appendChild(script);
            document.head.removeChild(script);
        }
    }

    #connectAttributes() {
        this.attributesValue.forEach((name) => {
            document
                .querySelectorAll(`
                    #${this.formIdValue} input[name="${name}"],
                    #${this.formIdValue} input[name="${name}[]"],
                    #${this.formIdValue} select[name="${name}"],
                    #${this.formIdValue} textarea[name="${name}"]
                `)
                .forEach((input) => {
                    input.setAttribute('data-action', `${this.identifier}#update`);
                });
        });
    }
}
