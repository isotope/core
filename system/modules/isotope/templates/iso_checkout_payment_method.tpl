<div class="payment_method">
<h2><?php echo $this->headline; ?></h2>
<p><?php echo $this->message; ?></p>
<ul>
<?php foreach($this->paymentMethods as $method): ?>
	<li><?php echo $method; ?></li>
<?php endforeach; ?>
</ul>
</div>