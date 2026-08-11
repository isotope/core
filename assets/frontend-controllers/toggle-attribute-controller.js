import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['receiver'];

    enable() {
        this.receiverTarget.disabled = false;
    }

    disable() {
        this.receiverTarget.disabled = true;
    }

    toggle () {
        this.receiverTarget.disabled = !this.receiverTarget.disabled;
    }
}
