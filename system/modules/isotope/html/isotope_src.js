/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


var Isotope =
{
	toggleAddressFields: function(el, id)
	{
		if (el.value == '0' && el.checked)
		{
			document.id(id).setStyle('display', 'block');
		}
		else
		{
			document.id(id).setStyle('display', 'none');
		}
	},

	/**
	 * Display a "loading data" message
	 * @param string
	 */
	displayBox: function(message, btnClose)
	{
		var box = document.id('iso_ajaxBox');
		var overlay = document.id('iso_ajaxOverlay');

		if (!overlay)
		{
			overlay = new Element('div').setProperty('id', 'iso_ajaxOverlay').injectInside(document.id(document.body));
		}

		if (!box)
		{
			box = new Element('div').setProperty('id', 'iso_ajaxBox').injectInside(document.id(document.body));
		}

		if (btnClose)
		{
			overlay.addEvent('click', Isotope.hideBox);
			box.addClass('btnClose').addEvent('click', Isotope.hideBox);
		}

		var scroll = window.getScroll().y;
		if (Browser.Engine.trident && Browser.Engine.version < 5) { var sel = $$('select'); for (var i=0; i<sel.length; i++) { sel[i].setStyle('visibility', 'hidden'); } }

		overlay.setStyle('display', 'block');
		overlay.setStyle('top', scroll + 'px');

		box.set('html', message);
		box.setStyle('display', 'block');
		box.setStyle('top', (scroll + 100) + 'px');
	},


	/**
	 * Hide the "loading data" message
	 */
	hideBox: function()
	{
		var box = document.id('iso_ajaxBox');
		var overlay = document.id('iso_ajaxOverlay');

		if (overlay)
		{
			overlay.setStyle('display', 'none').removeEvents('click');
		}

		if (box)
		{
			box.setStyle('display', 'none').removeEvents('click').removeClass('btnClose');
			if (Browser.Engine.trident && Browser.Engine.version < 5) { var sel = $$('select'); for (var i=0; i<sel.length; i++) { sel[i].setStyle('visibility', 'visible'); } }
		}
	},

	inlineGallery: function(el, elementId)
	{
		$$(('#'+elementId+'_mediumsize img')).set('src', el.href);

		$$(('#'+elementId+'_gallery div, #'+elementId+'_gallery img')).removeClass('active');

		el.addClass('active');
		el.getChildren().addClass('active');

		return false;
	}
};


var IsotopeProduct = new Class(
{
	
	Implements: Options,
	Binds: [ 'refresh' ],
	
	options:
	{
		language: 'en',
		action: 'fmd',
		page: 0,
		loadMessage: 'Loading product data …'
	},

	initialize: function(ajaxid, product, formId, attributes, options)
	{
		var self = this;
		self.setOptions(options);

		self.form = document.id(formId);
		if(!self.form) return;

		// MooTools handles IE compat for change event on radio and checkbox
		self.form.addEvent('change', self.refresh).set('send',
		{
			url: 'ajax.php?' + Object.toQueryString({
				action: self.options.action,
				id: ajaxid,
				language: self.options.language,
				page: self.options.page,
				product: product
			}),
			link: 'cancel',
			onRequest: Isotope.displayBox.pass(self.options.loadMessage),
			onSuccess: function(txt, xml)
			{
				Isotope.hideBox();

				var json = JSON.decode(txt);
				if(!json) return;
				
				// Update request token
				REQUEST_TOKEN = json.token;
				document.getElements('input[type="hidden"][name="REQUEST_TOKEN"]').set('value', json.token);

				json.content.each(function(option) {
					var old = document.id(option.id), js, html, el;
					if(!old) return;
					
					html = option.html.stripScripts(function(scripts) {
						js = scripts.replace(/<!--|\/\/-->|<!\[CDATA\[\/\/>|<!\]\]>/g, '');
					});
					el = new Element('div').set('html', html).getElement('*[id="' + option.id + '"]');
					
					if(el) el.replaces(old);
					if(js) Browser.exec(js);
				});

				// Update conditionalselect
				window.fireEvent('ajaxready');
				self.form.getElements('p.error').destroy();
			},
			onFailure: Isotope.hideBox
		});
	},

	refresh: function(event)
	{
		this.form.send();
	}
	
});

