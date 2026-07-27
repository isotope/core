export default class Limit {
    constructor(id, limit) {
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
