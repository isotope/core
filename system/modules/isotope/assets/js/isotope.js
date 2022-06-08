(function(_win, _doc, jQuery, MooTools) {
    "use strict";

    if (!jQuery && !MooTools) {
        var polyfill = _doc.createElement('script');
        polyfill.src = 'system/modules/isotope/assets/js/polyfills.min.js';

        var script = _doc.getElementsByTagName('script')[0];
        script.parentNode.insertBefore(polyfill, script);
    }

    function addEventListener(el, name, callback) {
        if (jQuery) {
            jQuery(el).on(name, callback);
        } else if (MooTools) {
            el.addEvent(name, callback);
        } else {
            el.addEventListener(name, callback, false);
        }
    }

    function dispatchEvent(name, data) {
        if (jQuery) {
            jQuery(window).trigger(jQuery.Event(name, data));
        } else if (MooTools) {
            window.fireEvent(name, data);
        } else {
            window.dispatchEvent(new CustomEvent(name, data));
        }
    }

    function serializeForm(form) {
        if (jQuery) {
            return jQuery(form).serialize();
        } else if (MooTools) {
            return form.toQueryString();
        } else {
            return formToQueryString(form);
        }
    }

    _win.Isotope = _win.Isotope || {

        toggleAddressFields: function (el, id) {
            if (el.value == '0' && el.checked) {
                _doc.getElementById(id).style.display = 'block';
            } else {
                _doc.getElementById(id).style.display = 'none';
            }
        },

        displayBox: function (message, btnClose) {
            var box = _doc.getElementById('iso_ajaxBox');
            var overlay = _doc.getElementById('iso_ajaxOverlay');

            if (!overlay) {
                overlay = _doc.createElement('div');
                overlay.setAttribute('id', 'iso_ajaxOverlay');
                _doc.body.appendChild(overlay);
            }

            if (!box) {
                box = _doc.createElement('div');
                box.setAttribute('id', 'iso_ajaxBox');
                _doc.body.appendChild(box);
            }

            if (btnClose) {
                addEventListener(overlay, 'click', Isotope.hideBox);
                addEventListener(box, 'click', Isotope.hideBox);
                if (!box.className.search(/btnClose/) != -1) {
                    box.className = box.className + ' btnClose';
                }
            }

            overlay.style.display = 'block';

            box.innerHTML = message;
            box.style.display = 'block';
        },

        hideBox: function () {
            var box = _doc.getElementById('iso_ajaxBox');
            var overlay = _doc.getElementById('iso_ajaxOverlay');

            if (overlay) {
                overlay.style.display = 'none';
                overlay.removeEventListener('click', Isotope.hideBox, false);
            }

            if (box) {
                box.style.display = 'none';
                box.removeEventListener('click', Isotope.hideBox, false);
                box.className = box.className.replace(/ ?btnClose/, '');
            }
        },

        inlineGallery: function (el, elementId) {
            var i;
            var parent = el.parentNode;
            var siblings = parent.parentNode.children;

            for (i = 0; i < siblings.length; i++) {
                if (siblings[i].getAttribute('data-type') == 'gallery'
                    && siblings[i].getAttribute('data-uid') == elementId
                    && siblings[i].getAttribute('class').search(/(^| )active($| )/) != -1
                ) {
                    siblings[i].setAttribute('class', siblings[i].getAttribute('class').replace(/ ?active/, ''));
                }
            }

            parent.setAttribute('class', parent.getAttribute('class') + ' active');
            _doc.getElementById(elementId).src = el.href;

            // Update the href for lightbox
            if (el.dataset.lightboxUrl) {
                _doc.getElementById(elementId).parentNode.href = el.dataset.lightboxUrl;
            }

            return false;
        },

        elevateZoom: function (el, elementId) {
            Isotope.inlineGallery(el, elementId);

            jQuery('#' + elementId).data('elevateZoom').swaptheimage(el.getAttribute('href'), el.getAttribute('data-zoom-image'));

            return false;
        },

        checkoutButton: function (form) {
            function disableButton(name) {
                try {
                    document.getElementsByName(name)[0].className = document.getElementsByName(name)[0].className + ' disabled';
                    document.getElementsByName(name)[0].onclick = function () { return false };
                } catch (e) {}
            }

            addEventListener(form, 'submit', function () {
                disableButton('nextStep');
                disableButton('previousStep');

                setTimeout(function () {
                    window.location.reload()
                }, 30000);
            });
        },

        initAwesomplete: function (id, searchField) {
            var requested = false;
            addEventListener(searchField, 'focus', function() {
                if (requested) return false;

                requested = true;

                var url = window.location.href + (document.location.search ? '&' : '?') + '&iso_autocomplete=' + id,
                    xhr = new XMLHttpRequest();

                xhr.open('GET', encodeURI(url));
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        new Awesomplete(searchField, {
                            list: JSON.parse(xhr.responseText)
                        });
                        searchField.focus();
                    }
                };

                xhr.send();
            });
        }
    };

    _win.IsotopeProducts = (function() {
        var loadMessage = 'Loading product data …';
        var forms = {};

        function initProduct(config) {
            var form = _doc.getElementById(config.formId);

            if (form && form.parentNode) {
                registerEvents(form.parentNode, config);
            }
        }

        function registerEvents(formParent, config) {
            var i, el,
                xhr = new XMLHttpRequest(),
                form = formParent.getElementsByTagName('form')[0];

            if (!form) return;

            xhr.open(form.getAttribute('method').toUpperCase(), form.getAttribute('action') || location.href);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    ajaxSuccess(xhr.responseText);
                } else if (xhr.status !== 200) {
                    Isotope.hideBox();
                }
            };


            function ajaxSuccess(txt) {
                var div = _doc.createElement('div'),
                    scripts = '',
                    script, i;

                txt = txt.replace(/<script([^>]*)>([\s\S]*?)<\/script>/gi, function(all, attr, code){
                    var type = attr.match(/type=['"]?([^"']+)/);

                    if (type !== null && type[1] !== 'text/javascript') {
                        return all;
                    }

                    scripts += code + '\n';
                    return '';
                });

                div.innerHTML = txt;

                // Remove all error messages
                var skip = 0;
                var errors = div.getElementsByTagName('p');
                while (skip < errors.length) {
                    if (errors[skip].className.search(/(^| )error( |$)/) !== -1) {
                        errors[skip].parentNode.removeChild(errors[skip]);
                    } else {
                        skip++;
                    }
                }

                formParent.innerHTML = '';
                while (div.childNodes.length > 0) {
                    formParent.appendChild(div.childNodes[0]);
                }

                registerEvents(formParent, config);

                Isotope.hideBox();

                if (scripts) {
                    script = _doc.createElement('script');
                    script.text = scripts;
                    _doc.head.appendChild(script);
                    _doc.head.removeChild(script);
                }

                dispatchEvent('isotopeProductReload', { detail: config });
            }

            function submitForm() {
                if (xhr.readyState > 1) {
                    xhr.abort();
                }

                Isotope.displayBox(loadMessage);
                xhr.send(serializeForm(form));
            }

            if (config.attributes) {
                for (i=0; i<config.attributes.length; i++) {
                    el = _doc.getElementById(('ctrl_'+config.attributes[i]+'_'+config.formId));
                    if (el) {
                        addEventListener(el, 'change', submitForm);
                    }
                }
            }

            forms[config.formId] = submitForm;
        }

        return {
            'attach': function(products) {
                var i;

                // Check if products is an array
                if (Object.prototype.toString.call(products) === '[object Array]' && products.length > 0) {
                    for (i=0; i<products.length; i++) {
                        initProduct(products[i]);
                    }
                }
            },

            'setLoadMessage': function(message) {
                loadMessage = message || 'Loading product data …';
            },

            'submit': function(formId) {
                if (!forms.hasOwnProperty(formId)) {
                    throw 'Form "'+formId+'" does not exist.';
                }

                forms[formId]();
            }
        };
    })();
})(window, document, window.jQuery, window.MooTools);
