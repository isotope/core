<script language="javascript" type="text/javascript">

window.addEvent('domready', function() {
	
	
	IsotopeFrontend.loadGallery(<?php echo $this->productJson; ?>);
	
	IsotopeFrontend.loadProductBinders('<?php echo $this->mId; ?>');

});
</script>