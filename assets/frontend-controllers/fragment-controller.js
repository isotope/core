import { Controller } from "@hotwired/stimulus";
import { ProductQuery } from '../scripts/_productquery';
import axios from '../scripts/_axios';

export default class extends Controller {
    static values = {
        id: {
            type: String,
            required: true,
        },
        filters: Array,
    };

    query (event) {
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

        this.#load(url.toString());
    }

    update (event) {
        event.preventDefault();

        this.#load(new URL(window.location).toString());
    }

    async #load (url) {
        const timer = setTimeout(() => {
            this.element.setAttribute('aria-busy', 'true');
        }, 500)

        const response = await axios.get(url, {
            headers: {
                'Isotope-Fragment': this.idValue,
            }
        });

        clearTimeout(timer);

        const div = document.createElement('div');
        div.innerHTML = response.data;
        this.element.replaceWith(div.firstElementChild);
    }
}
