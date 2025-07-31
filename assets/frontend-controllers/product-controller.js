import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static values = {
        formId: String,
        options: Array,
    };

    static targets = ['form'];

    connect () {
        this.xhr = new XMLHttpRequest();

        if (!this.hasFormTarget) return;

        this.xhr.open(this.formTarget.getAttribute('method').toUpperCase(), this.formTarget.getAttribute('action') || location.href);
        this.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        this.xhr.onload = () => {
            if (this.xhr.status === 200) {
                this.#ajaxSuccess(this.xhr.responseText);
            } else if (this.xhr.status !== 200) {
                // Isotope.hideBox();
            }
        };

        this.#connectOptions();
    }

    disconnect() {
        this.xhr.abort();
    }

    submit() {
        if (this.xhr.readyState > 1) {
            this.xhr.abort();
        }

        this.element.setAttribute('aria-busy', 'true');

        // Isotope.displayBox(loadMessage);
        this.xhr.send(new URLSearchParams(new FormData(this.formTarget).entries()).toString());
    }

    #ajaxSuccess(txt) {
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

        // Isotope.hideBox();

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
            const el = document.querySelector(`#ctrl_${this.formIdValue}_${option}`)

            if (el) {
                el.setAttribute('data-action', `${this.identifier}#submit`)
            }
        })
    }
}
