<script language="javascript" type="text/javascript">

window.addEvent('domready', function() {
	
	
	Isotope.loadGallery(<?php echo $this->productJson; ?>);
	
	Isotope.loadProductBinders('<?php echo $this->mId; ?>');

});
</script>