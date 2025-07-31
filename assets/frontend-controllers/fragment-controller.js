import { Controller } from "@hotwired/stimulus";
import { ProductQuery } from '../scripts/_productquery';

export default class extends Controller {
    static values = {
        id: {
            type: String,
            required: true,
        },
        filters: Array,
    };

    update (event) {
        const url = new URL(window.location);

        if (this.hasFiltersValue) {
            const query = new ProductQuery();
            let load = false;

            for (const filter of event.detail.query.getConfigsForIds(this.filtersValue)) {
                query.apply(filter);
                load = true;
            }

            if (!load && !ProductQuery.fromLocation().getConfigsForIds(this.filtersValue).length) {
                return;
            }

            event.preventDefault();

            url.search = query.toString();
        }

        this.#load(url);
    }

    async #load (url) {
        const timer = setTimeout(() => {
            this.element.setAttribute('aria-busy', 'true');
        }, 500)

        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Isotope-Fragment': this.idValue,
            }
        });

        clearTimeout(timer);

        const div = document.createElement('div');
        div.innerHTML = await response.text();
        this.element.replaceWith(div.firstElementChild);
    }
}
