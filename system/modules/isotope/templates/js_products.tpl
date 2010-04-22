<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function() {
	Isotope.loadGallery(<?php echo $this->productJson; ?>);
	Isotope.loadProductBinders('<?php echo $this->mId; ?>');
});
//--><!]]>
</script>