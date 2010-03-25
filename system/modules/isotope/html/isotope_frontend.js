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
		var variantsDiv = document.id('variants_container');
		
		var image_gallery = document.id('image_gallery');
		
		var arrVariants = new Array;
				
		if(image_gallery)
		{						
			var imageElements = image_gallery.getElements('a');
						
			imageElements.each(function(item, index) {
				item.addEvent('click', function(event){
					event.stop();
					
					var mainImage = document.id('image_main');
					
					mainImage.set('html', IsotopeFrontend.replaceImage(item, 'medium'));					
				});
			});
		}
				
		if(variantsDiv)
		{
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
							
							IsotopeFrontend.loadGallery(objProduct);
						}
					}).send('action=fmd&' + 'id=' + mId + '&variant=' + item.value);
				});
			});	
		}
		
	},
	
	loadGallery: function(objProduct)
	{			
		var imagesHtml = new String();
		
		if(objProduct.images)
		{				
			var image_main = document.id('image_main');
			
			//image update handler
			objProduct.images.each(function(item, index){
								
				switch(index)
				{
					case 0:												
						if(image_main)
						{			
							image_main.set('html', IsotopeFrontend.replaceImage(item, 'medium', true));							
						}
						break;										
				}				
			
				imagesHtml += IsotopeFrontend.replaceImage(item, 'gallery');
				
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
		
		var image_gallery = document.id('image_gallery');
		
		if(image_gallery)
		{
			images = image_gallery.getElements('img');
			
			images.each(function(item, index){
				item.addEvent('click', function(event){
						event.stop();
																			
						image_main.set('html', IsotopeFrontend.replaceImage(objProduct.images[index], 'medium', true));					
					
				});
			});
		}
		
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
	
	replaceImage: function(image, thumbnailType, isMain)
	{			
		if( isMain==null)
			isMain=false;
			
		var sizeType = thumbnailType+'_size';
		
		if(isMain)
			return html = "<a href=\"" + image['large'] + "\" title=\"" + image['alt'] + "\" rel=\"lightbox\"><img src=\"" + image[thumbnailType] + "\" alt=\"" + image['alt'] + "\"" + image[sizeType] + " \/><\/a>\n";
		else	
			return html = "<img src=\"" + image[thumbnailType] + "\" alt=\"" + image['alt'] + "\"" + image[sizeType] + "/>\n";

	},

	insertProductList: function(html)
	{		
		var productList = document.id('product_list');
			
		if(productList)
		{
			productList.set('html', html);	
		}
		
	},
	
	getQueryString: function(perPage)
	{
		var keyword = $('ctrl_for').get('value').toString();
		
		return '&order_by=' + $('ctrl_order_by').get('value') + '&for=' + keyword.replace('%', '') + '&per_page=' + perPage;		
	}
	
};


var IsotopeProduct = new Class(
{
	Binds: ['refresh'],
	
	initialize: function(module, product, attributes)
	{
		this.form = document.id(('iso_product_'+product)).set('send',
		{
			url: ('ajax.php?action=fmd&id='+module+'&product='+product),
			link: 'cancel',
			onRequest: function()
			{
				IsotopeFrontend.showLoader();
			},
			onSuccess: function(txt, xml)
			{
				IsotopeFrontend.hideLoader();
				
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
			},
			onFailure: function()
			{
				IsotopeFrontend.hideLoader();
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

