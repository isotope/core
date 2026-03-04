import { Controller } from "@hotwired/stimulus";
import axios from '../scripts/_axios';

export default class extends Controller {
    static targets = ['receiver'];

    enable() {
        this.receiverTarget.disabled = false;
    }

    disable() {
        this.receiverTarget.disabled = true;
    }
}
