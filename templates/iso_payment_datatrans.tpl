<form id="datatrans<?php echo $this->id; ?>" action="<?php echo $this->action; ?>" method="post">
<?php foreach( $this->params as $k => $v ): ?>
<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
<?php endforeach; ?>
<noscript>
<input type="submit" value="<?php echo $this->slabel; ?>">
</noscript>
</form>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent('domready' , function() {
  document.id('datatrans<?php echo $this->id; ?>').submit();
});
//--><!]]>
</script>