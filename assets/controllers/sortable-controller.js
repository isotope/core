import { Controller } from "@hotwired/stimulus"
import Sortable from "sortablejs"

export default class extends Controller {
    static values = {
        animation: Number,
        handle: String,
    }

    connect() {
        this.sortable = new Sortable(this.element, {
            animation: this.animationValue || 150,
            handle: this.handleValue || undefined,
            onUpdate: () => {
                this.dispatch('update');
            },
        })
    }

    disconnect() {
        this.sortable.destroy()
        this.sortable = undefined
    }
}
