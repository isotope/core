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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
  
 
var TableLookupWizard = new Class(
{
	Binds: ['send', 'show', 'checked'],
	
	initialize: function(name)
	{
		this.element = name;
		
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
			onSuccess: this.show
		}).addEvent('keyup', this.send);
	},
	
	send: function()
	{
		$$(('#ctrl_'+this.element+' .search input.tl_text')).setStyle('background-image', 'url(system/modules/tablelookupwizard/html/loading.gif)');
		$(('ctrl_'+this.element)).send();
	},
	
	show: function(responseText, responseXML)
	{
		$$(('#ctrl_'+this.element+' .search input.tl_text')).setStyle('background-image', 'none');
		$$(('#ctrl_'+this.element+' tr.found')).each( function(el)
		{
			el.destroy();
		});
	
		var rows = Elements.from(responseText, false);
		$$(('#ctrl_'+this.element+' tbody')).adopt(rows);
		rows.each( function(row)
		{
			row.getElement('input[type=checkbox]').addEvent('change', this.checked);
		}.bind(this));
	},
	
	checked: function(event)
	{
		if (event.target.checked)
		{
			event.target.getParent('tr').removeClass('found').inject($$(('#ctrl_'+this.element+' tr.search'))[0], 'before');
		}
		else
		{
			event.target.getParent('tr').destroy();
			$(('ctrl_'+this.element)).send();
		}
	}
});

