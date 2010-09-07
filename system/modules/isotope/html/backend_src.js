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

	/**
	 * Media Manager
	 * @param object
	 * @param string
	 * @param string
	 */
	mediaManager: function(el, command, id)
	{
		var table = $(id).getFirst('table');
		var tbody = table.getFirst('tbody');
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

				if (first.type == 'hidden' || first.type == 'text' || first.type == 'textarea')
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
	 * Image watermark wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	imageWatermarkWizard: function(el, command, id)
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

				if (first.type == 'select-one')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
				}
				else if (first.type == 'text' || first.type == 'checkbox')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']')
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
		$$('a.header_isotope_tools').addEvent('click', function(e)
		{
			$('isotopetoolsmenu').setStyle('display', 'block');
			return false;
		});

		// Hide context menu 
		$(document.body).addEvent('click', function()
		{
			$('isotopetoolsmenu').setStyle('display', 'none');
		});
	},
	
	initializeToolsButton: function()
	{
		// Hide the tool buttons
		$$('#tl_listing .isotope-tools').each(function(el)
		{
			el.addClass('invisible');
		});

		// Add trigger to edit buttons
		$$('a.isotope-contextmenu').each(function(el)
		{
			if (el.getNext('a.isotope-tools'))
			{
				el.removeClass('invisible').addEvent('click', function(e)
				{
					if ($defined($('isotope-contextmenu')))
					{
						$('isotope-contextmenu').destroy();
					}
	
					var div = new Element('div',
					{
						'id': 'isotope-contextmenu',
						'styles': {
							'top': (el.getPosition().y + 22),
							'display': 'block'
						}
					});
					
					el.getAllNext('a.isotope-tools').each( function(el2)
					{
						var im2 = el2.getFirst('img');
						div.set('html', (div.get('html')+'<a href="'+ el2.href +'" title="'+ el2.title +'">'+ el2.get('html') +' '+ im2.alt +'</a>'));
					});
					
					div.inject($(document.body));
					div.setStyle('left', el.getPosition().x - (div.getSize().x / 2));
					
					return false;
				});
			}
		});

		// Hide context menu 
		$(document.body).addEvent('click', function()
		{
			if ($defined($('isotope-contextmenu')))
			{
				$('isotope-contextmenu').destroy();
			}
		});
	}
};

window.addEvent('domready', function()
{
	Isotope.addInteractiveHelp();
	Isotope.initializeToolsMenu();
	Isotope.initializeToolsButton();
});

