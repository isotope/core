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

Isotope.inlineGallery = function(el, elementId)
{
    $$(('#'+elementId+'_mediumsize img')).set('src', el.href);

    $$(('#'+elementId+'_gallery div, #'+elementId+'_gallery img')).removeClass('active');

    el.addClass('active');
    el.getChildren().addClass('active');

    return false;
};


var IsotopeProduct = new Class(
{
    Implements: Options,
    Binds: ['refresh'],
    options: {
        language: 'en',
        action: 'fmd',
        page: 0,
        loadMessage: 'Loading product data …'
    },

    initialize: function(ajaxid, product, formId, attributes, options)
    {
        this.setOptions(options);

        this.form = document.id(formId);

        if (this.form)
        {
            this.form.set('send',
            {
                url: ('ajax.php?action='+this.options.action+'&id='+ajaxid+'&language='+this.options.language+'&page='+this.options.page+'&product='+product),
                link: 'cancel',
                onRequest: function()
                {
                    Isotope.displayBox(this.options.loadMessage);
                }.bind(this),
                onSuccess: function(txt, xml)
                {
                    Isotope.hideBox();

                    var json = JSON.decode(txt);

                    // Update request token
                    REQUEST_TOKEN = json.token;
                    document.getElements('input[type="hidden"][name="REQUEST_TOKEN"]').set('value', json.token);

                    json.content.each( function(option)
                    {
                        var oldEl = document.id(option.id);
                        if (oldEl)
                        {
                            var newEl = null;
                            new Element('div').set('html', option.html).getElements('*').each( function(child) {
                                if (child.get('id') == option.id)
                                {
                                    newEl = child;
                                }
                            });

                            if (newEl)
                            {
                                if (newEl.hasClass('radio_container'))
                                {
                                    newEl.getElements('input.radio').each( function(option, index) {
                                        option.addEvent('click', this.refresh);
                                    }.bind(this));
                                }

                                newEl.cloneEvents(oldEl).replaces(oldEl);
                            }
                        }
                    }.bind(this));

                    // Update conditionalselect
                    window.fireEvent('ajaxready');
                    $$(('#'+formId+' p.error')).destroy();
                }.bind(this),
                onFailure: function()
                {
                    Isotope.hideBox();
                }
            });

            attributes.each( function(el,index)
            {
                el = document.id(el);
                if (el && el.hasClass('radio_container'))
                {
                    el.getElements('input.radio').each( function(option) {
                        option.addEvent('click', this.refresh);
                    }.bind(this));
                }
                else if(el)
                {
                    el.addEvent('change', this.refresh);
                }
            }.bind(this));
        }
    },

    refresh: function(event)
    {
        this.form.send();
    }
});

