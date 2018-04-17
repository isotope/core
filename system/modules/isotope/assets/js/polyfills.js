/**
 * EventListener Polyfill for IE8
 * @see https://gist.github.com/jonathantneal/3748027
 */
!window.addEventListener && (function (WindowPrototype, DocumentPrototype, ElementPrototype, addEventListener, removeEventListener, dispatchEvent, registry) {
    WindowPrototype[addEventListener] = DocumentPrototype[addEventListener] = ElementPrototype[addEventListener] = function (type, listener) {
        var target = this;

        registry.unshift([target, type, listener, function (event) {
            event.currentTarget = target;
            event.preventDefault = function () { event.returnValue = false };
            event.stopPropagation = function () { event.cancelBubble = true };
            event.target = event.srcElement || target;

            listener.call(target, event);
        }]);

        this.attachEvent("on" + type, registry[0][3]);
    };

    WindowPrototype[removeEventListener] = DocumentPrototype[removeEventListener] = ElementPrototype[removeEventListener] = function (type, listener) {
        for (var index = 0, register; register = registry[index]; ++index) {
            if (register[0] == this && register[1] == type && register[2] == listener) {
                return this.detachEvent("on" + type, registry.splice(index, 1)[0][3]);
            }
        }
    };

    WindowPrototype[dispatchEvent] = DocumentPrototype[dispatchEvent] = ElementPrototype[dispatchEvent] = function (eventObject) {
        return this.fireEvent("on" + eventObject.type, eventObject);
    };
})(Window.prototype, HTMLDocument.prototype, Element.prototype, "addEventListener", "removeEventListener", "dispatchEvent", []);

(function () {
    if (typeof window.CustomEvent === "function") return false;

    function CustomEvent(event, params) {
        params = params || {bubbles: false, cancelable: false, detail: undefined};
        var evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }

    CustomEvent.prototype = window.Event.prototype;

    window.CustomEvent = CustomEvent;
})();

function formToQueryString(form) {
    var queryString = [],
        fields = form.querySelectorAll('input, select, textarea'),
        i, x, o, el, type, value, options;

    for (i=0; i<fields.length; i++) {
        el = fields[i];
        type = el.type.toLowerCase();
        if (!el.name || el.disabled || type == 'submit' || type == 'reset' || type == 'file' || type == 'image') continue;

        value = [];

        if (el.tagName == 'SELECT') {
            options = el.querySelectorAll('option');
            for (o=0; o<options.length; o++) {
                if (options[o].selected) {
                    value.push(options[o].value);
                }
            }

        } else if ((type != 'radio' && type != 'checkbox') || el.checked) {
            value.push(el.value);
        }

        for (x=0; x<value.length; x++) {
            if (typeof value[x] != 'undefined') queryString.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(value[x]));
        }
    }

    return queryString.join('&');
}
