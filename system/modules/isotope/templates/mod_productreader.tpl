
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
	
	function replaceMainImage(html)
	{				
		$('image_main').set('html', html);
	}
	
	function replaceGallery(html)
	{				
		$('image_gallery').set('html', html);
	}
	
	var productForm = $('productForm');
	
	productForm.addEvent('submit',function(event){ event.stop(); }); 
	
	var ctrlVariants = $('ctrl_product_variants');
	
	var parentProduct = $('ctrl_product_id');
	
	ctrlVariants.addEvent('change', function(event) {
		event.stop();
						
		var req = new Request({
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
		}).send();	
});
</script>
<?php endif; ?>