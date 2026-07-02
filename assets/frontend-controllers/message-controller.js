import Notification from "@stimulus-components/notification"

export default class extends Notification {
    stop(e) {
        e.preventDefault();
        e.stopPropagation();
    }
}
