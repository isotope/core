export default class FilterValue {
    constructor(attribute, operator, value) {
        this.attribute = attribute;
        this.operator = operator;
        this.value = Array.isArray(value) ? value : [value];
    }

    toString() {
        let { operator } = this;
        let { value } = this;

        if (/\w/.test(operator)) {
            operator = `(${operator})`;
        }

        if (Array.isArray(value)) {
            value = value.join(',');
        }

        return `${this.attribute}${operator}${value}`;
    }
}
