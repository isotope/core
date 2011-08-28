<div class="shipping_method">
<h2><?php echo $this->headline; ?></h2>
<p><?php echo $this->message; ?></p>
<ul>
<?php foreach($this->shippingMethods as $method): ?>
	<li><?php echo $method; ?></li>
<?php endforeach; ?>
</ul>
<?php if (strlen($this->error)): ?>
<p class="error"><?php echo $this->error; ?></p><?php endif; ?>
</div>