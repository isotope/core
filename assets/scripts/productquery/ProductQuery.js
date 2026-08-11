import Filter from './Filter';
import Limit from './Limit';
import Search from './Search';
import Sorting from './Sorting';

export default class ProductQuery {
    #configs = [];

    constructor () {
    }

    static fromLocation() {
        const instance = new ProductQuery();
        const { search } = window.location;

        if (!search.startsWith('?')) {
            return instance;
        }

        for (const [key, value] of new URLSearchParams(window.location.search)) {
            const match = String(key).match(/^_([fslq])(\w*)$/);

            if (match === null) {
                continue;
            }

            switch (match[1]) {
                case 'f': {
                    const filter = Filter.parse(match[2] || '', value);

                    if (filter) {
                        instance.apply(filter);
                    }
                    break;
                }

                case 's':
                    instance.apply(Sorting.parse(match[2] || '', value));
                    break;

                case 'l':
                    instance.apply(new Limit(match[2] || '', value));
                    break;

                case 'q':
                    instance.apply(new Search(match[2] || '', value));
                    break;

                default:
                    throw new Error('Unknown match');
            }
        }

        return instance;
    }

    apply(config) {
        this.#configs = this.#configs.filter((c) => c.constructor !== config.constructor || c.id !== config.id);
        this.#configs.push(config);

        return this;
    }

    getConfigsForIds(ids) {
        return this.#configs.filter((c) => ids.map((id) => id).includes(c.id));
    }

    getFilter(id) {
        return this.#configs.find((c) => c instanceof Filter && c.id === id) || new Filter(id);
    }

    toString() {
        return this.toURLSearchParams().toString();
    }

    toURLSearchParams(params) {
        const searchParams = params || new URLSearchParams();

        for (const config of this.#configs) {
            const value = config.value();

            if (value) {
                searchParams.set(config.key(), value);
            } else {
                searchParams.delete(config.key());
            }
        }

        return searchParams;
    }
}
