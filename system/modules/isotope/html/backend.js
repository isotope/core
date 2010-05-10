/**
 * @copyright  Winans Creative 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
var Isotope = 
{		
	/**
	 * Media Manager
	 * @param object
	 * @param string
	 * @param string
	 */
	mediaManager: function(el, command, id)
	{
		var table = $(id);
		var tbody = table.getFirst().getNext();
		var parent = $(el).getParent('tr');
		var rows = tbody.getChildren();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'up':
				parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
				break;

			case 'down':
				parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
				break;

			case 'delete':
				parent.destroy();
				break;
		}

		rows = tbody.getChildren();

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'hidden' || first.type == 'textarea')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
				}
			}
		}
	},
	
	/**
	 * Attribute wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	attributeWizard: function(el, command, id)
	{
		var container = $(id);
		var parent = $(el).getParent();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'up':
				if (!parent.getPrevious() || parent.getPrevious().hasClass('fixed'))
				{
					parent.injectInside(container);
				}
				else
				{
					parent.injectBefore(parent.getPrevious());
				}
				break;

			case 'down':
				if (parent.getNext())
				{
					parent.injectAfter(parent.getNext());
				}
				else
				{
					var fel = container.getFirst();

					if (fel.hasClass('fixed'))
					{
						fel = fel.getNext();
					}

					parent.injectBefore(fel);
				}
				break;

		}
	},
	
		/**
	 * Module wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	surchargeWizard: function(el, command, id)
	{
		var table = $(id);
		var tbody = table.getFirst().getNext();
		var parent = $(el).getParent('tr');
		var rows = tbody.getChildren();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'copy':
				var tr = new Element('tr');
				var childs = parent.getChildren();

				for (var i=0; i<childs.length; i++)
				{
					var next = childs[i].clone(true).injectInside(tr);
					next.getFirst().value = childs[i].getFirst().value;
				}

				tr.injectAfter(parent);
				break;

			case 'up':
				parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
				break;

			case 'down':
				parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
				break;

			case 'delete':
				(rows.length > 1) ? parent.destroy() : null;
				break;
		}

		rows = tbody.getChildren();

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'select-one' || first.type == 'text' || first.type == 'checkbox')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
				}
			}
		}
	}, 
	
	/**
	 * Toggle checkbox group
	 * @param object
	 * @param string
	 */
	toggleCheckboxGroup: function(el, id)
	{
		var cls = $(el).className;
		var status = $(el).checked ? 'checked' : '';

		if (cls == 'tl_checkbox')
		{
			$$('#' + id + ' .tl_checkbox').each(function(checkbox)
			{
				if (!checkbox.disabled)
					checkbox.checked = status;
			});
		}
		else if (cls == 'tl_tree_checkbox')
		{
			$$('#' + id + ' .parent .tl_tree_checkbox').each(function(checkbox)
			{
				if (!checkbox.disabled)
					checkbox.checked = status;
			});
		}

		Backend.getScrollOffset();
	},
	
	/**
	 * Add the interactive help
	 */
	addInteractiveHelp: function()
	{
		$$('a.tl_tip').each(function(el)
		{
			if (el.retrieve('complete'))
			{
				return;
			}

			el.addEvent('mouseover', function()
			{
				el.timo = setTimeout(function()
				{
					var box = $('tl_helpBox');

					if (!box)
					{
						box = new Element('div').setProperty('id', 'tl_helpBox').injectInside($(document.body));
					}

					var scroll = el.getTop();

					box.set('html', el.get('longdesc'));
					box.setStyle('display', 'block');
					box.setStyle('top', (scroll + 18) + 'px');
				}, 1000);
			});

			el.addEvent('mouseout', function()
			{
				var box = $('tl_helpBox');

				if (box)
				{
					box.setStyle('display', 'none');
				}

				clearTimeout(el.timo);
			});

			el.store('complete', true);
		});
	},
	
	
	inheritFields: function(fields, label)
	{
		var injectError = false;
		
		fields.each(function(name, i)
		{
			var el = $(('ctrl_'+name));
			
			if (el)
			{
				var parent = el.getParent('div').getFirst('h3');
				
				if (!parent && el.match('.tl_checkbox_single_container'))
				{
					parent = el;
				}
				
				if (!parent)
				{
					injectError = true;
					return;
				}
				
				parent.addClass('inherit');
					
				var check = $('ctrl_inherit').getFirst(('input[value='+name+']'));
				
				check.setStyle('float', 'right').inject(parent);
				$('ctrl_inherit').getFirst(('label[for='+check.get('id')+']')).setStyles({'float':'right','padding-right':'5px', 'font-weight':'normal'}).set('text', label).inject(parent);
				
				check.addEvent('change', function(event) {
					var element = $(('ctrl_'+event.target.get('value')));
					
					if (element.match('.tl_checkbox_single_container'))
					{
						element.getFirst('input').disabled = event.target.checked;
					}
					else
					{
						element.setStyle('display', (event.target.checked ? 'none' : 'block'));
					}
				});
				
				if (el.match('.tl_checkbox_single_container'))
				{
					el.getFirst('input').readonly = check.checked;
				}
				else
				{
					el.setStyle('display', (check.checked ? 'none' : 'block'));
				}
			}
		});
		
		if (!injectError)
		{
			$('ctrl_inherit').getParent('div').setStyle('display', 'none');
		}
	},
	
	productWizard: function(name)
	{
		$$(('#ctrl_'+name+' .jserror')).setStyle('display', 'none');
		$$(('#ctrl_'+name+' .search')).setStyle('display', 'table-row');
		
		$$(('#ctrl_'+name+' tbody tr')).each( function(row)
		{
			var check = row.getElement('input[type=checkbox]');
			if (check)
			{
				check.addEvent('change', function(event)
				{
					event.target.getParent('tr').destroy();
					$(('ctrl_'+name)).send();
				});
			}
		});
		
		$(('ctrl_'+name)).set('send',
		{
			url: ('ajax.php?action=ffl&id='+name),
			link: 'cancel',
			onRequest: function() {
				$$(('#ctrl_'+name+' .search input.tl_text')).setStyle('background-image', 'url(system/modules/isotope/html/loading.gif)');
			},
			onSuccess: function(responseText, responseXML)
			{
				$$(('#ctrl_'+name+' .search input.tl_text')).setStyle('background-image', 'none');
				$$(('#ctrl_'+name+' tr.found')).each( function(el)
				{
					el.destroy();
				});
			
				var rows = Elements.from(responseText, false);
				$$(('#ctrl_'+name+' tbody')).adopt(rows);
				rows.each( function(row)
				{
					row.getElement('input[type=checkbox]').addEvent('change', function(event)
					{
						if (event.target.checked)
						{
							event.target.getParent('tr').removeClass('found').inject($$(('#ctrl_'+name+' tr.search'))[0], 'before');
						}
						else
						{
							event.target.getParent('tr').destroy();
							$(('ctrl_'+name)).send();
						}
					});
				});
			}
		}).addEvent('keyup', function(event)
		{
			$(('ctrl_'+name)).send();
		});
	},
	
	initializeToolsMenu: function()
	{
		if ($$('#tl_buttons .isotope-tools').length < 1)
			return;
			
		$$('#tl_buttons .header_isotope_tools').setStyle('display', 'inline');
		
		var tools = $$('#tl_buttons .isotope-tools').clone();
		
		var buttons = [];
		var nodes = $('tl_buttons').childNodes;
		for( var i=0; i<nodes.length; i++ )
		{
			if (!nodes[i])
				continue;
				
			if (nodes[i].hasClass && nodes[i].hasClass('isotope-tools'))
			{
				i++;
				continue;
			}
			
			if (nodes[i].clone)
			{
				buttons.push(nodes[i].clone());
			}
			else
			{
				buttons.push(nodes[i]);
			}
		}
		
		if (!buttons[buttons.length-1].clone)
			buttons.erase(buttons[buttons.length-1]);
		
		$('tl_buttons').empty().adopt(buttons);
		
		var div = new Element('div',
		{
			'id': 'isotopetoolsmenu',
			'styles': {
				'top': ($$('a.header_isotope_tools')[0].getPosition().y + 22)
			}
		}).adopt(tools);
		
		div.inject($(document.body));
		div.setStyle('left', $$('a.header_isotope_tools')[0].getPosition().x - 7);
		
		// Add trigger to tools buttons
		$$('a.header_isotope_tools').addEvent('contextmenu', function(e)
		{
			$('isotopetoolsmenu').setStyle('display', 'block');
			return false;
		}).addEvent('click', function(e)
		{
			$('isotopetoolsmenu').setStyle('display', 'block');
			return false;
		});

		// Hide context menu 
		$(document.body).addEvent('click', function()
		{
			$('isotopetoolsmenu').setStyle('display', 'none');
		});
	}
};

window.addEvent('domready', function()
{
	Isotope.addInteractiveHelp();
	Isotope.initializeToolsMenu();
});
