
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<form id="productForm" action="<?php echo $this->action; ?>" method="<?php echo (!$this->disableAjax ? "get" : "post"); ?>">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formId; ?>" />

<?php echo $this->product; ?>

</div>
</form>

</div>
<?php if(!$this->disableAjax): ?>
<div id="ajaxOverlay" style="display: none;">&nbsp;</div>
<div id="ajaxLoader" class="ctrl_ajax_loader" style="display: none;">
<p>Loading...<br /><?php echo $this->loadingMessage; ?></p>
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
		return html = "<a href=\"" + image['large'] + "\" title=\"" + image['desc'] + "\" rel=\"lightbox\"><img src=\"" + image['thumbnail'] + "\" alt=\"" + image['alt'] + "\"" + image['thumbnail_size'] + "/></a>\n";
		
	}
	
	function replaceGallery(image)
	{	
		return html = "<a href=\"" + image['large'] + "\" title=\"" + image['desc'] + "\" rel=\"lightbox\"><img src=\"" + image['gallery'] + "\" alt=\"" + image['alt'] + "\"" + image['gallery_size'] + "/></a>\n";
		
	}
	
	var productForm = $('productForm');
	
	productForm.addEvent('submit',function(event){ event.stop(); }); 
	
	var ctrlVariants = $('ctrl_product_variants');
	
	var parentProduct = $('ctrl_product_id');
	
	ctrlVariants.addEvent('change', function(event) {
		event.stop();
		
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
		}).send('<?php echo $this->ajaxParams; ?>' + '&product_id=' + parentProduct.value + '&variant=' + this.value);
	});
	
	 			
	/*	var req = new Request({
			method: 'get',
			url: 'ajax.php',
			urlencoded: true,
			data: '<?php echo $this->ajaxParams; ?>' + '&product_id=' + parentProduct.value + '&variant=' + this.value + '&container=image_main',
			onRequest: showLoader(),
			onSuccess: function(responseText, responseXML) { replaceMainImage(responseText); hideLoader(); }
		}).send();	
		
		var req = new Request({
			method: 'get',
			url: 'ajax.php',
			urlencoded: true,
			data: '<?php echo $this->ajaxParams; ?>' + '&product_id=' + parentProduct.value + '&variant=' + this.value + '&container=image_gallery',
			onRequest: showLoader(),
			onSuccess: function(responseText, responseXML) { replaceGallery(responseText); hideLoader(); }
		}).send();	*/
});
</script>
<?php endif; ?>