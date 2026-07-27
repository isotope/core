import FilterValue from './FilterValue';

export default class Filter {
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

            const [, attribute, operator, v] = match;
            let op = operator;

            if (operator.startsWith('(') && operator.endsWith(')')) {
                op = operator.substring(1, operator.length - 1);
            }

            values.push(new FilterValue(attribute, op, v.split(',')));
        }

        return values.length ? new Filter(id, values) : null;
    }

    set(attribute, operator, value) {
        const v = String(value);

        this.unset(attribute, operator);

        if (v) {
            this.values.push(new FilterValue(attribute, operator, v));
        }

        return this;
    }

    unset(attribute, operator) {
        this.values = this.values.filter((v) => v.attribute !== attribute || v.operator !== operator);

        return this;
    }

    add(attribute, operator, value) {
        const filterValue = this.values.find((v) => v.attribute === attribute && v.operator === operator);
        const v = String(value);

        if (!filterValue) {
            this.set(attribute, operator, v);
        } else if (!filterValue.value.includes(v)) {
            filterValue.value.push(v);
        }

        return this;
    }

    remove(attribute, operator, value) {
        const filterValue = this.values.find((f) => f.attribute === attribute && f.operator === operator);

        if (!filterValue) {
            return;
        }

        const v = String(value);
        filterValue.value = filterValue.value.filter((f) => f !== v);

        if (!filterValue.value.length) {
            this.unset(attribute, operator);
        }
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
        return Object.values(this.values)
            .map((v) => v.toString())
            .join(';');
    }
}
