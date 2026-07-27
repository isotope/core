export default class SortingValue {
    constructor(attribute, desc = false) {
        this.attribute = attribute;
        this.desc = desc;
    }

    toString() {
        return `${this.attribute}${this.desc ? '!' : ''}`;
    }
}
