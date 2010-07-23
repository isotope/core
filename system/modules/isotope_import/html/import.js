/**
 * Class ImportRequest
 *
 * Provide methods to handle Ajax requests.
 */
var ImportRequest =
{
	/**
	 * Store the live update ID
	 * @param string
	 * @return boolean
	 */
	startImport: function(el, method)
	{
		if(method=='start')
		{
			$('tl_import').set('html','<div id="tl_header" class="spin">Starting Import</div>');
		}
		var request = new Request.JSON(
		{
			url: window.location.href,
			data: 'isAjax=1&action=startImport&method=' + method,
			onRequest: '',
			onComplete: function(obj, txt)
			{
				if(obj)
				{
					if (obj.method != 'finished')
					{
						ImportRequest.startImport(el,obj.method);
						var old = $('tl_import').get('html');
						$('tl_import').set('html',old + '<div class="tl_info">'+ obj.message +'</div>');
					}
					else
					{
						var old = $('tl_import').get('html');
						$('tl_import').set('html',old + '<div class="tl_info">'+ obj.message +'</div><div class="tl_confirm">Update Successful</div>');
						$('tl_header').set('html','Finished');
						$('tl_header').removeClass('spin');
					}
				}
				else
				{
					ImportRequest.getError(txt);
				}
			}
		}).send();
	},
	
	getError: function(error)
	{
		if(error)
		{
			var old = $('tl_import').get('html');
			$('tl_import').set('html',old + '<div class="tl_error">'+ error +'</div>');
			$('tl_header').set('html','An Error Occurred');
			$('tl_header').removeClass('spin');
		} else
		{
			alert("If you are seeing this message, something went really wrong during the import, like a database connection failure, or a page refresh or something. It shouldn't happen, but if it does, you will want to start over.");
			var old = $('tl_import').get('html');
			$('tl_import').set('html',old + '<div class="tl_error">An Error Occurred</div>');
			$('tl_header').set('html','An Error Occurred');
			$('tl_header').removeClass('spin');
		}
	}

}

var BackendImport =
{
	/**
	 * Category wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	categoryWizard: function(el, command, id)
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

					if (next.getFirst().type == 'checkbox')
					{
						next.getFirst().checked = childs[i].getFirst().checked ? 'checked' : '';
						if (Browser.Engine.trident && Browser.Engine.version < 5) next.innerHTML = next.innerHTML.replace(/CHECKED/ig, 'checked="checked"');
					}
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
		var fieldnames = new Array('value', 'label', 'default');

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'text' || first.type == 'checkbox' || first.get('tag') == 'select')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']')
				}
			}
		}
	}

}
	