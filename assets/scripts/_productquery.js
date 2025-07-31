
export class ProductQuery {
    #configs = [];

    static fromLocation () {
        const instance = new ProductQuery();
        const search = window.location.search;

        if (!search.startsWith('?')) {
            return instance;
        }

        for (const [key, value] of new URLSearchParams(window.location.search)) {
            const match = String(key).match(/^_([fsl])(\w+)$/);

            if (null === match) {
                continue;
            }

            switch (match[1]) {
                case 'f':
                    const filter = Filter.parse(match[2], value);

                    if (filter) {
                        instance.apply(filter);
                    }
                    break;

                case 's':
                    instance.apply(Sorting.parse(match[2], value))
                    break;

                case 'l':
                    instance.apply(new Limit(match[2], value))
                    break;
            }
        }

        return instance;
    }

    apply(config) {
        this.#configs = this.#configs.filter((c) => c.constructor !== config.constructor || c.id !== config.id);
        this.#configs.push(config);

        return this;
    }

    getConfigsForIds (ids) {
        return this.#configs.filter((c) => ids.map(id => id).includes(c.id));
    }

    getFilter (id) {
        return this.#configs.find((c) => c instanceof Filter && c.id === id) || new Filter(id);
    }

    toString() {
        return this.toURLSearchParams().toString();
    }

    toURLSearchParams(searchParams) {
        if (!searchParams) {
            searchParams = new URLSearchParams();
        }

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

export class Filter {
    constructor(id, values = []) {
        this.id = id;
        this.values = values;
    }

    static parse(id, value) {
        const values = [];

        for (const config of value.split(';')) {
            const match = config.match(/^(\w*)(\([^)]+\)|\W)(.+)$/);

            if (!match) {
                continue;
            }

            let [,attribute, operator, value] = match;

            if (operator.startsWith('(') && operator.endsWith(')')) {
                operator = operator.substring(1, operator.length - 1);
            }

            values.push(new FilterValue(attribute, operator, value.split(',')));
        }

        return values.length ? new Filter(id, values) : null;
    }

    set(attribute, operator, value) {
        value = String(value);

        this.unset(attribute, operator);

        if (value) {
            this.values.push(new FilterValue(attribute, operator, value));
        }

        return this;
    }

    unset(attribute, operator) {
        this.values = this.values.filter((v) => v.attribute !== attribute || v.operator !== operator);

        return this;
    }

    add(attribute, operator, value) {
        const filterValue = this.values.find((v) => v.attribute === attribute && v.operator === operator);
        value = String(value);

        if (!filterValue) {
            this.set(attribute, operator, value);
        } else if (!filterValue.value.includes(value)) {
            filterValue.value.push(value);
        }

        return this;
    }

    remove(attribute, operator, value) {
        const filterValue = this.values.find((f) => f.attribute === attribute && f.operator === operator);

        if (!filterValue) {
            return;
        }

        value = String(value);
        filterValue.value = filterValue.value.filter((v) => v !== value)

        if (!filterValue.value.length) {
            this.unset(attribute, operator);
        }

        return this;
    }

    has(attribute, operator, value) {
        const filterValue = this.values.find((f) => f.attribute === attribute && f.operator === operator);

        if (!filterValue) {
            return false;
        }

        return filterValue.value.includes(String(value));
    }

    toggle(attribute, operator, value) {
        if (this.has(attribute, operator, value)) {
            this.remove(attribute, operator, value);
        } else {
            this.add(attribute, operator, value);
        }

        return this;
    }

    key() {
        return `_f${this.id}`;
    }

    value() {
        return Object.values(this.values).map(v => v.toString()).join(';');
    }
}

class FilterValue {
    constructor (attribute, operator, value) {
        this.attribute = attribute;
        this.operator = operator;
        this.value = Array.isArray(value) ? value : [value];
    }

    toString() {
        let operator = this.operator;
        let value = this.value;

        if (/\w/.test(operator)) {
            operator = `(${operator})`;
        }

        if (Array.isArray(value)) {
            value = value.join(',');
        }

        return `${this.attribute}${operator}${value}`;
    }
}

export class Sorting {
    constructor (id, values = []) {
        this.id = id;
        this.values = values;
    }

    static parse(id, value) {
        const values = [];

        for (const config of value.split(';')) {
            const match = value.match(/^(\w+)(!?)$/);

            if (!match) {
                continue;
            }

            const [,attribute, desc] = match;
            values.push(new SortingValue(attribute, !!desc));
        }

        return values.length ? new Sorting(id, values) : null;
    }

    add(attribute, desc = false) {
        this.values.push(new SortingValue(attribute, desc));

        return this;
    }

    key() {
        return `_s${this.id}`;
    }

    value() {
        return this.values.map(v => v.toString()).join(';');
    }
}

class SortingValue {
    constructor (attribute, desc = false) {
        this.attribute = attribute;
        this.desc = desc;
    }

    toString() {
        return `${this.attribute}${this.desc ? '!' : ''}`;
    }
}

export class Limit {
    constructor (id, limit) {
        this.id = id;
        this.limit = Number(limit);
    }

    key() {
        return `_l${this.id}`;
    }

    value() {
        return `${this.limit}`;
    }
}
