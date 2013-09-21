/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


var Isotope = Isotope || {};

Isotope.toggleAddressFields = function(el, id)
{
    if (el.value == '0' && el.checked) {
        document.getElementById(id).style.display = 'block';
    } else {
        document.getElementById(id).style.display = 'none';
    }
};

/**
 * Display a "loading data" message
 * @param string
 */
Isotope.displayBox = function(message, btnClose)
{
    var box = document.getElementById('iso_ajaxBox');
    var overlay = document.getElementById('iso_ajaxOverlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.setAttribute('id', 'iso_ajaxOverlay');
        document.body.appendChild(overlay);
    }

    if (!box) {
        box = document.createElement('div');
        box.setAttribute('id', 'iso_ajaxBox');
        document.body.appendChild(box);
    }

    if (btnClose) {
        overlay.addEventListener('click', Isotope.hideBox, false);
        box.addEventListener('click', Isotope.hideBox, false);
        if (!box.className.test(/btnClose/)) {
            box.className = box.className + ' btnClose';
        }
    }

    var scroll = window.getScroll().y;

    overlay.style.display = 'block';

    box.innerHTML = message;
    box.style.display = 'block';
    box.style.top = ((scroll + 100) + 'px');
};


/**
 * Hide the "loading data" message
 */
Isotope.hideBox = function()
{
    var box = document.getElementById('iso_ajaxBox');
    var overlay = document.getElementById('iso_ajaxOverlay');

    if (overlay) {
        overlay.style.display = 'none';
        overlay.removeEventListener('click', Isotope.hideBox, false);
    }

    if (box) {
        box.style.display = 'none';
        box.removeEventListener('click', Isotope.hideBox, false);
        box.className = box.className.replace(/ ?btnClose/, '');
    }
};

Isotope.inlineGallery = function(el, elementId) {
"use strict";

    var i;
    var parent = el.parentNode;
    var siblings = parent.parentNode.children;

    for (i=0; i<siblings.length; i++) {
        if (siblings[i].className.test(/image_container/) && siblings[i].className.test(/active/)) {
            siblings[i].className = siblings[i].className.replace(/ ?active/, '');
        }
    }

    parent.className = parent.className + ' active';
    document.getElementById(elementId).src = el.href;

    return false;
};





var IsotopeProducts = (function() {
"use strict";

    var loadMessage = 'Loading product data …';
    var callbacks = [];

    function initProduct(config) {

        var form = document.getElementById(config.formId);

        if (form) {
            registerEvents(form, config);
        }
    }


    function registerEvents(form, config) {

        var i, el;

        document.id(form).set('send', {
            url: window.location.href,
            link: 'cancel',
            onRequest: Isotope.displayBox.pass(loadMessage),
            onSuccess: function(txt, xml)
            {
                Isotope.hideBox();

                var div = document.createElement('div');
                div.innerHTML = txt;
                var newForm = div.firstChild;

                form.parentNode.replaceChild(newForm, form);
                registerEvents(newForm, config);
            },
            onFailure: Isotope.hideBox
        });

        if (config.attributes) {
            for (i=0; i<config.attributes.length; i++) {
                el = document.getElementById(('ctrl_'+config.attributes[i]+'_'+config.formId));
                if (el) {
                    el.addEventListener('change', function() {
                        form.send();
                    }, false);
                }
            }
        }
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

        /**
         * Callbacks are used to handle special products
         */
        'registerCallback': function(callback) {
            callbacks.push(callback);
        },

        /**
         * Overwrite the default message
         */
        'setLoadMessage': function(message) {
            loadMessage = message || 'Loading product data …';
        }
    };
})();
