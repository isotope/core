<div class="shipping_method">
<h2><?php echo $this->headline; ?></h2>
<p><?php echo $this->message; ?></p>
<div class="radio_container">
<?php foreach($this->shippingMethods as $method): ?>
<span>
	<input id="ctrl_shipping_module_<?php echo $method['id']; ?>" type="radio" class="radio" name="shipping[module]" value="<?php echo $method['id']; ?>"<?php echo $method['checked']; ?> />
	<label for="ctrl_shipping_module_<?php echo $method['id']; ?>"><?php echo $method['label'] . $method['price']; ?></label>
	<?php if ($method['note'] != ''): ?>
	<div class="clear">&nbsp;</div><br /><div class="shippingNote"><strong>Note:</strong><br /><?php echo $method['note']; ?></div>
	<?php endif; ?>
	<?php if ($method['options'] != ''): ?>
	<div class="clear">&nbsp;</div><br /><div class="shippingOptions"><strong>Options:</strong><br /><?php echo $method['options']; ?></div>
	<?php endif; ?>
</span>
<?php endforeach; ?>
</div>
<?php if (strlen($this->error)): ?>
<p class="error"><?php echo $this->error; ?></p><?php endif; ?>
</div>