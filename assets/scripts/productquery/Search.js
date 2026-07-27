export default class Search {
    constructor(id, keywords) {
        this.id = id;
        this.keywords = keywords;
    }

    key() {
        return `_q${this.id}`;
    }

    value() {
        return this.keywords;
    }
}
