import { Controller } from "@hotwired/stimulus";
import { ProductQuery, Filter, Sorting, Limit, Search } from '../scripts/_productquery';

export default class extends Controller {
    static targets = ['search'];
    static values = {
        id: {
            type: String,
            required: true,
        },
    };

    set (event) {
        const query = ProductQuery.fromLocation();
        const filter = query
            .getFilter(this.idValue)
            .set(...this.#getFilterParams(event))
        ;

        query.apply(filter);

        this.#exec(query, event);
    }

    unset (event) {
        const query = ProductQuery.fromLocation();
        const filter = query
            .getFilter(this.idValue)
            .unset(...this.#getFilterParams(event))
        ;

        query.apply(filter);

        this.#exec(query, event);
    }

    add (event) {
        const query = ProductQuery.fromLocation();
        const filter = query.getFilter(this.idValue);
        const [attribute, operator, values] = this.#getFilterParams(event);

        for (const value of values) {
            filter.add(attribute, operator, value);
        }

        query.apply(filter);

        this.#exec(query, event);
    }

    remove (event) {
        const query = ProductQuery.fromLocation();
        const filter = query.getFilter(this.idValue);
        const [attribute, operator, values] = this.#getFilterParams(event);

        for (const value of values) {
            filter.remove(attribute, operator, value);
        }

        query.apply(filter);

        this.#exec(query, event);
    }

    toggle (event) {
        const query = ProductQuery.fromLocation();
        const filter = query.getFilter(this.idValue);
        const [attribute, operator, values] = this.#getFilterParams(event);

        for (const value of values) {
            filter.toggle(attribute, operator, value);
        }

        query.apply(filter);

        this.#exec(query, event);
    }

    sort (event) {
        const query = ProductQuery.fromLocation();
        const value = event.params.value || event.target.value;

        if (!value) {
            throw new Error('Missing value for product query sorting. Either set Stimulus param "value" or make sure the event target has a "value" attribute (e.g. is an HTML form field).')
        }

        const [attribute, direction] = value.split(':');
        query.apply(new Sorting(this.idValue).add(attribute, 'DESC' === direction));

        this.#exec(query, event);
    }

    limit (event) {
        const query = ProductQuery.fromLocation();
        const value = event.params.value || event.target.value;

        if (!value) {
            throw new Error('Missing value for product query limit. Either set Stimulus param "value" or make sure the event target has a "value" attribute (e.g. is an HTML form field).')
        }

        query.apply(new Limit(this.idValue, value.value));

        this.#exec(query, event);
    }

    search (event) {
        if (!this.hasSearchTarget) {
            throw new Error('No search widget found.');
        }

        const query = ProductQuery.fromLocation();

        query.apply(new Search(this.searchTarget.value))

        this.#exec(query, event);
    }

    reset (event) {
        this.#exec(new ProductQuery(), event);
    }

    #getFilterParams(event) {
        const name = event.params.name || event.target.name || '';
        const operator = event.params.operator;
        let value = event.params.value || event.target.value;

        if (!operator) {
            throw new Error('Missing operator for product query filter. Make sure to set the Stimulus param "operator".')
        }

        if (value) {
            value = String(value).split(',');
        }

        return [name, operator, value];
    }

    #exec (query, event) {
        const url = new URL(window.location);
        url.search = query.toString();
        window.history.pushState({}, '', url.toString());

        if (this.dispatch('update', { target: event?.target, detail: { query }, cancelable: true }).defaultPrevented) {
            event?.preventDefault();

            return;
        }

        window.history.popState();

        window.location.search = query.toString()
    }
}
