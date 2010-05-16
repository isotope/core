/**
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fredrbliss@gmail.com>
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
	},
};


var IsotopeProduct = new Class(
{
	Implements: Options,
	Binds: ['refresh'],
	options: {
		loadMessage: 'Loading product data …'
	},
	
	initialize: function(module, product, attributes, options)
	{
		this.setOptions(options);
		
		this.form = document.id(('iso_product_'+product)).set('send',
		{
			url: ('ajax.php?action=fmd&id='+module+'&product='+product),
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

