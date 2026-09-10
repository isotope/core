import { Controller } from '@hotwired/stimulus';

export default class Notification extends Controller {
    static values = {
        delay: {
            type: Number,
            default: 5000,
        },
    };

    initialize() {
        this.hide = this.hide.bind(this);
    }

    connect() {
        if (this.hiddenValue === false) {
            this.show();
        }
    }

    show() {
        if (this.delayValue) {
            this.timeout = setTimeout(this.hide, this.delayValue);
        }
    }

    hide(e) {
        e.preventDefault();

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.element.remove();
    }

    stop(e) {
        e.preventDefault();
        e.stopPropagation();
    }
}
