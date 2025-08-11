import { Controller } from "@hotwired/stimulus";
import axios from '../scripts/_axios';

export default class extends Controller {
    static values = {
        id: {
            type: String,
            required: true,
        },
        type: String,
    };

    static targets = ['form'];

    #abortController = null;

    async formAction(event) {
        event.preventDefault();

        this.#abortController?.abort();

        this.#setBusy(true);

        const data = new FormData(this.formTarget);

        if (event.submitter?.name && event.submitter?.value) {
            data.set(event.submitter.name, event.submitter.value);
        }

        await this.#submitForm(data);

        const isPrevented = this.#dispatchSubmit({ target: event.submitter, detail: { form: this.formTarget, submitter: event.submitter, data } });

        if (!isPrevented) {
            window.location.reload();
        }

        this.#setBusy(false);

    }

    async linkAction(event) {
        event.preventDefault();

        this.#setBusy(true);

        this.#abortController = new AbortController();

        await axios.get(event.target.href, {
            signal: this.#abortController.signal,
            headers: {
                'Isotope-Fragment': this.idValue,
            }
        });

        const isPrevented = this.#dispatchSubmit({ target: event.target });

        if (!isPrevented) {
            window.location.reload();
        }

        this.#setBusy(false);
    }

    #setBusy(state) {
        this.element.setAttribute('aria-busy', state ? 'true' : 'false');

        if (this.hasFormTarget) {
            this.formTarget.disabled = state;
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
                'Isotope-Fragment': this.idValue,
                'Content-Type': 'application/x-www-form-urlencoded',
            },
        });
    }

    #dispatchSubmit(options, type) {
        let isPrevented = this.dispatch('submit', options).defaultPrevented;

        if (this.hasTypeValue) {
            isPrevented = this.dispatch(`submit.${this.typeValue}`, options).defaultPrevented || isPrevented;
        }

        return isPrevented;
    }
}
