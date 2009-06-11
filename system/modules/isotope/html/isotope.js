/**
 * @copyright  Winans Creative / Fred Bliss 2009
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
	}
};


/**
 * Class AjaxRequestIsotope
 *
 * Provide methods to handle ajax-related tasks for Isotope back end widgets.
 * @copyright  Fred Bliss / Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
var ProductsOptionWizard =
{
		/**
	 * Table wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	tableWizard: function(el, command, id, name)
	{			
		var table = $(id);
		var tbody = table.getFirst();
		var rows = tbody.getChildren();
		var parentTd = $(el).getParent();
		var parentTr = parentTd.getParent();
		var cols = parentTr.getChildren();
		var index = 0;
		
		for (var i=0; i<cols.length; i++)
		{
			if (cols[i] == parentTd)
			{
				
				break;
			}

			index++;
		}

		ProductsOptionWizard.getScrollOffset();

		switch (command)
		{
			case 'rcopy':
				var tr = new Element('tr');
				var childs = parentTr.getChildren();

				for (var i=0; i<childs.length; i++)
				{
					var next = childs[i].clone(true).injectInside(tr);
					next.getFirst().value = childs[i].getFirst().value;
					next.getFirst().id = childs[i].getFirst().value;
				}

				tr.injectAfter(parentTr);
				break;

			case 'rup':
				parentTr.getPrevious().getPrevious() ? parentTr.injectBefore(parentTr.getPrevious()) : parentTr.injectInside(tbody);
				break;

			case 'rdown':
				parentTr.getNext() ? parentTr.injectAfter(parentTr.getNext()) : parentTr.injectBefore(tbody.getFirst().getNext());
				break;

			case 'rdelete':
				(rows.length > 2) ? parentTr.destroy() : null;
				break;

			case 'ccopy':
				for (var i=0; i<rows.length; i++)
				{
					var current = rows[i].getChildren()[index];
					var next = current.clone(true).injectAfter(current);
					next.getFirst().value = current.getFirst().value;
					
					next.getFirst().setAttribute('id',current.getFirst().id);
				}
				break;

			case 'cmovel':
				if (index > 0)
				{
					for (var i=0; i<rows.length; i++)
					{
						var current = rows[i].getChildren()[index];
						current.injectBefore(current.getPrevious());
					}
				}
				else
				{
					for (var i=0; i<rows.length; i++)
					{
						var current = rows[i].getChildren()[index];
						current.injectBefore(rows[i].getLast());
					}
				}
				break;

			case 'cmover':
				if (index < (cols.length - 2))
				{
					for (var i=0; i<rows.length; i++)
					{
						var current = rows[i].getChildren()[index];
						current.injectAfter(current.getNext());
					}
				}
				else
				{
					for (var i=0; i<rows.length; i++)
					{
						var current = rows[i].getChildren()[index];
						current.injectBefore(rows[i].getFirst());
					}
				}
				break;

			case 'cdelete':
				if (cols.length > 2)
				{
					for (var i=0; i<rows.length; i++)
					{
						rows[i].getChildren()[index].destroy();
					}
				}
				break;
		}

		rows = tbody.getChildren();
		
		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();
					
			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();
						
				if (first && first.type == 'select-one')
				{
					first.name = first.name.replace(/\[[0-9]+\][[0-9]+\]/ig, '[' + (i-1) + '][' + j + ']');
										
				}
				
				if (first && first.id == 'value_div')
				{
					first.name = first.name.replace(/\[[0-9]+\][[0-9]+\]/ig, '[' + (i-1) + '][' + j + ']');				
				}
				
			}
		}

		ProductsOptionWizard.tableWizardResize();
	},
	
	getOptionValues: function(el, id, name)
	{
		el.blur();	
		
		var item_value = el.value; 
				
		var re1='.*?';	// Non-greedy match on filler
     	var re2='(\\d+)';	// Integer Number 1
        var re3='.*?';	// Non-greedy match on filler
        var re4='(\\d+)';	// Integer Number 2

        var p = new RegExp(re1+re2+re3+re4,["i"]);
        var m = p.exec(el.name);
      
      	if (m != null)
      	{
          var xcoord=m[1];
          var ycoord=m[2];
   		}
		
				
		new Request(
		{
			url: window.location.href,
			data: 'isAjax=1&action=addPOAttributeValues&aid=' + item_value + '&parent=' + name + '&r=' + xcoord + '&c=' + ycoord,
			onStateChange: ProductsOptionWizard.displayBox('Loading data ...'),			
			onComplete: function(txt, xml)
			{
									
				var currDiv = $('value_div');
				
				if($defined(currDiv))
				{
					currDiv.destroy();
				}
					
				div = new Element('div');
				div.setProperty('id','value_div');
				div.setProperty('name',id + '_values[' + xcoord + '][' + ycoord + ']');
				div.set('html',txt);
				
				div.injectAfter(el);
										
				
				ProductsOptionWizard.hideBox();
   			}
		}).send();

		return false;	
	},
	
	/*
	 * Resize table wizard fields on focus
	 */
	tableWizardResize: function()
	{
		$$('.tl_tablewizard textarea').each(function(el)
		{
			el.set('morph', { duration: 200 });

			el.addEvent('focus', function()
			{
				el.setStyle('position', 'absolute');
				el.morph(
				{
					'height': '166px',
					'width': '356px',
					'margin-top': '-50px',
					'margin-left': '-107px'
				});
				el.setStyle('z-index', '1');
			});

			el.addEvent('blur', function()
			{
				el.setStyle('z-index', '0');
				el.morph(
				{
					'height': '66px',
					'width': '142px',
					'margin-top': '1px',
					'margin-left': '0'
				});
				setTimeout(function() { el.setStyle('position', ''); }, 250);
			});
		});
	},


	/**
	 * Display a "loading data" message
	 * @param string
	 */
	displayBox: function(message)
	{
		var box = $('tl_ajaxBox');
		var overlay = $('tl_ajaxOverlay');

		if (!overlay)
		{
			overlay = new Element('div').setProperty('id', 'tl_ajaxOverlay').injectInside($(document.body));
		}

		if (!box)
		{
			box = new Element('div').setProperty('id', 'tl_ajaxBox').injectInside($(document.body));
		}

		var scroll = window.getScrollTop();
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
		var box = $('tl_ajaxBox');
		var overlay = $('tl_ajaxOverlay');

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


	/**
	 * Get current scroll offset and store it in a cookie
	 */
	getScrollOffset: function()
	{
		document.cookie = "BE_PAGE_OFFSET=" + window.getScrollTop() + "; path=/";
	}

	
}

