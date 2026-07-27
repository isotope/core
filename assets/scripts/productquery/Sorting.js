import SortingValue from './SortingValue';

export default class Sorting {
    constructor(id, values = []) {
        this.id = id;
        this.values = values;
    }

    static parse(id, value) {
        const values = [];

        for (const config of value.split(';')) {
            const match = config.match(/^(\w+)(!?)$/);

            if (!match) {
                continue;
            }

            const [, attribute, desc] = match;
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
        return this.values.map((v) => v.toString()).join(';');
    }
}
