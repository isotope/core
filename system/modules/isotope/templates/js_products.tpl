<div id="ajaxOverlay" style="display: none;">&nbsp;</div>
<div id="ajaxLoader" class="ctrl_ajax_loader" style="display: none;">
<p><?php echo $this->ajaxLoadingImage; ?> <?php echo $this->ajaxLoadingMessage; ?></p>
</div>
<script language="javascript" type="text/javascript">

window.addEvent('domready', function() {
	
	function showLoader()
	{
		$('ajaxOverlay').setStyle('display','block');
		$('ajaxLoader').setStyle('display','block');
	}
	
	function hideLoader()
	{
		$('ajaxOverlay').setStyle('display','none');
		$('ajaxLoader').setStyle('display','none');
	}
	
	function replaceMainImage(image)
	{				
		return html = "<a href=\"" + image['large'] + "\" title=\"" + image['desc'] + "\" rel=\"lightbox\"><img src=\"" + image['thumbnail'] + "\" alt=\"" + image['alt'] + "\"" + image['thumbnail_size'] + "/><\/a>\n";
		
	}
	
	function replaceGallery(image)
	{	
		return html = "<a href=\"" + image['large'] + "\" title=\"" + image['desc'] + "\" rel=\"lightbox\"><img src=\"" + image['gallery'] + "\" alt=\"" + image['alt'] + "\"" + image['gallery_size'] + "/><\/a>\n";
		
	}
	
	//productForm.addEvent('submit',function(event){ event.stop(); }); 
	
	var productForm = $('productForm');
	
	var ctrlVariants = $('ctrl_product_variants');
	var ctrlProductId = $('ctrl_product_id');
			
	ctrlVariants.addEvent('change', function(event) {
		event.stop();
		
		if(this.value)
		{
			var productId = this.value;
		}
		else
		{
			var productId = ctrlProductId.value;
		}
		
		var request = new Request.JSON({
			url: 'ajax.php',
			method: 'get',
			onRequest: showLoader(),
			onComplete: function(objProduct) {
				
				hideLoader();
		
				//direct update of elements with html that might need replacing, such as price, description, etc.
				for(var key in objProduct)
				{					
					var currElement = document.id('product_' + key);
										
					if(currElement)
					{
						currElement.set('html', objProduct[key]);		
					}
				}
				
				var imagesHtml = new String();
				
				$('image_gallery').set('html', '');		
				//image update handler
				objProduct.images.each(function(item, index){
					
					switch(index)
					{
						case 0:							
							$('image_main').set('html', replaceMainImage(item));							
							break;
						default:							
							imagesHtml += replaceGallery(item);							
							break;
					}				
					
					if(imagesHtml.length>0)
					{
						$('image_gallery').set('html', imagesHtml);
					}
				});
			}
		}).send('<?php echo $this->ajaxParams; ?>&product_id=' + ctrlProductId.value + '&variant=' + productId);
	});
});
</script>