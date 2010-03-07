/**
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fredrbliss@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
var IsotopeFrontend =
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
	
	loadProductBinders: function(mId)
	{	
		var productForm = $('productForm');
		
		var variantsDiv = $('variants_container');
		
		var ctrlProductId = $('ctrl_product_id');
		
		var arrVariants = new Array;
		
		arrVariants = variantsDiv.getElements('select');
		
		arrVariants.each(function(item, index) {
		
			item.addEvent('change', function(event) {
				event.stop();
										
				var request = new Request.JSON({
					url: 'ajax.php',
					method: 'get',
					onRequest: IsotopeFrontend.showLoader(),
					onComplete: function(objProduct) {
						
						IsotopeFrontend.hideLoader();
				
						//direct update of elements with html that might need replacing, such as price, description, etc.
						for(var key in objProduct)
						{					
							var currElement = document.id('ajax_' + key);
												
							if(currElement)
							{
								currElement.set('html', objProduct[key]);		
							}
						}
						
						var imagesHtml = new String();
						
						var image_gallery = document.id('image_gallery');
						
						if(image_gallery)
						{
							image_gallery.set('html', '');		
						}
						//image update handler
						objProduct.images.each(function(item, index){
							
							switch(index)
							{
								case 0:	
									var image_main = document.id('image_main');
									
									if(image_main)
									{			
										image_main.set('html', IsotopeFrontend.replaceImage(item, 'thumbnail'));							
									}
									break;
								default:							
									imagesHtml += IsotopeFrontend.replaceImage(item, 'gallery');							
									break;
							}				
							
							if(imagesHtml.length>0)
							{
								var image_gallery = document.id('image_gallery');
								
								if(image_gallery)
								{
									image_gallery.set('html', imagesHtml);
								}
							}
						});
					}
				}).send('action=fmd&' + 'id=' + mId + '&variant=' + item.value);
			});
		});	
		
	},
	
	showLoader: function()
	{
		var box = $('ajaxLoader');
		var overlay = $('ajaxOverlay');

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

		box.setStyle('display', 'block');
		box.setStyle('top', (scroll + 100) + 'px');
		
	},
	
	hideLoader: function()
	{
		var box = $('ajaxLoader');
		var overlay = $('ajaxOverlay');

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
	
	gup: function( name, url )
	{
	  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	  var regexS = "[\\?&]"+name+"=([^&#]*)";
	  var regex = new RegExp( regexS );
	  var results = regex.exec( url );
	  if( results == null )
		return "";
	  else
		return results[1];
	},
	
	modifyPagination: function(ajaxParams)
	{
		var paginationLinks = $$('div.pagination ul li').getChildren('a');
				
		paginationLinks.each(function(item, index){
			
			var qString = item.get('href').toString();
			
			var pageNum = IsotopeFrontend.gup('page',qString);
			
			item.set('href','#');			
			
			item.addEvent('click', function(event) {
				event.stop();
				var req = new Request({
					method: 'get',
					url: 'ajax.php',
					urlencoded: true,
					data: ajaxParams + IsotopeFrontend.getQueryString($('ctrl_per_page').get('value')) + IsotopeFrontend.setPage(pageNum),
					onRequest: IsotopeFrontend.showLoader(),
					onSuccess: function(responseText, responseXML) { IsotopeFrontend.insertProductList(responseText); IsotopeFrontend.hideLoader(); }
				}).send();
			});		
					
		});
	},
	
	insertProductList: function(html)
	{
		$('product_list').set('html', html);
		IsotopeFrontend.modifyPagination();	
	},
	
	getQueryString: function(perPage)
	{
		var keyword = $('ctrl_for').get('value').toString();
		
		return '&order_by=' + $('ctrl_order_by').get('value') + '&for=' + keyword.replace('%', '') + '&per_page=' + perPage;		
	},
	
	setPage: function(i)
	{
		return '&page=' + i;	
	},
	
	replaceImage: function(image, thumbnailType)
	{				
		var sizeType = thumbnailType+'_size';
		
		return html = "<a href=\"" + image['large'] + "\" title=\"" + image['desc'] + "\" rel=\"lightbox\"><img src=\"" + image[thumbnailType] + "\" alt=\"" + image['alt'] + "\"" + image[sizeType] + "/><\/a>\n";
		
	}
	
	
};