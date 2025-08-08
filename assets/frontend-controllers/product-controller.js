import { Controller } from "@hotwired/stimulus"
import axios from '../scripts/_axios';

export default class extends Controller {
    static values = {
        formId: String,
        options: Array,
    };

    static targets = ['form'];

    #abortController = null;

    connect () {
        this.xhr = new XMLHttpRequest();

        if (!this.hasFormTarget) return;

        this.#connectOptions();
    }

    disconnect() {
        this.#abortController?.abort();
    }

    update() {
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

        this.dispatch(
            event.submitter?.value || 'submit',
            { target: event.submitter, detail: { form: this.formTarget, submitter: event.submitter, data }/*, cancelable: true*/ }
        );
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
        let txt = (await axiosPromise).data;

        var div = document.createElement('div'),
            scripts = '',
            script, i;

        txt = txt.replace(/<script([^>]*)>([\s\S]*?)<\/script>/gi, function(all, attr, code){
            var type = attr.match(/type=['"]?([^"']+)/);

            if (type !== null && type[1] !== 'text/javascript') {
                return all;
            }

            scripts += code + '\n';
            return '';
        });

        div.innerHTML = txt;

        // Remove all error messages
        var skip = 0;
        var errors = div.getElementsByTagName('p');
        while (skip < errors.length) {
            if (errors[skip].className.search(/(^| )error( |$)/) !== -1) {
                errors[skip].parentNode.removeChild(errors[skip]);
            } else {
                skip++;
            }
        }

        this.element.replaceWith(...div.childNodes);

        if (scripts) {
            script = document.createElement('script');
            script.text = scripts;
            document.head.appendChild(script);
            document.head.removeChild(script);
        }

        // window.dispatchEvent(new CustomEvent('isotopeProductReload', { detail: config }));
    }

    #connectOptions() {
        this.optionsValue.forEach((option) => {
            document
                .querySelector(`#ctrl_${this.formIdValue}_${option}`)
                ?.setAttribute('data-action', `${this.identifier}#update`);
        })
    }
}
