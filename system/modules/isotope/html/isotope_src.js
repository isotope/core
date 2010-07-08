/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

 
var Isotope =
{
	toggleAddressFields: function(el, id)
	{
		if (el.value == '0' && el.checked)
		{
			$(id).setStyle('display', 'block');
		}
		else
		{
			$(id).setStyle('display', 'none');
		}
	},
		
	/**
	 * Display a "loading data" message
	 * @param string
	 */
	displayBox: function(message)
	{
		var box = $('iso_ajaxBox');
		var overlay = $('iso_ajaxOverlay');

		if (!overlay)
		{
			overlay = new Element('div').setProperty('id', 'iso_ajaxOverlay').injectInside($(document.body));
		}

		if (!box)
		{
			box = new Element('div').setProperty('id', 'iso_ajaxBox').injectInside($(document.body));
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
		var box = $('iso_ajaxBox');
		var overlay = $('iso_ajaxOverlay');

		if (overlay)
		{
			overlay.setStyle('display', 'none');
		}

		if (box)
		{
			box.setStyle('display', 'none');
			if (Browser.Engine.trident && Browser.Engine.version < 5) { var sel = $$('select'); for (var i=0; i<sel.length; i++) { sel[i].setStyle('visibility', 'visible'); } }
		}
	}
};


var IsotopeProduct = new Class(
{
	Implements: Options,
	Binds: ['refresh'],
	options: {
		language: 'en',
		loadMessage: 'Loading product data …'
	},
	
	initialize: function(module, product, attributes, options)
	{
		this.setOptions(options);
		
		this.form = document.id(('iso_product_'+product)).set('send',
		{
			url: ('ajax.php?action=fmd&id='+module+'&language='+this.options.language+'&product='+product),
			link: 'cancel',
			onRequest: function()
			{
				Isotope.displayBox(this.options.loadMessage);
			}.bind(this),
			onSuccess: function(txt, xml)
			{
				Isotope.hideBox();
				
				JSON.decode(txt).each( function(option)
				{
					var oldEl = document.id(option.id);
					
					if (oldEl)
					{
						var newEl = new Element('div').set('html', option.html).getFirst(('#'+option.id));
						
						if (newEl)
						{
							newEl.cloneEvents(oldEl).replaces(oldEl);
						}
					}
				});
				
				// Update conditionalselect
				window.fireEvent('ajaxready');
			},
			onFailure: function()
			{
				Isotope.hideBox();
			}
		});
		
		attributes.each( function(el,index)
		{
			if ($(el))
			{
				$(el).addEvent('change', this.refresh);
			}
		}.bind(this));
	},
	
	refresh: function(event)
	{
		this.form.send();
	}
});

