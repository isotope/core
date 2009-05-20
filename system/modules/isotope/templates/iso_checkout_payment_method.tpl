<div>
	<?php if($this->noPaymentMethod): ?>
		<span><?php echo $this->noPaymentMethod; ?></span>
	<?php else: ?>
	<ul>
		<?php foreach($this->paymentMethods as $method): ?>
			<li><?php echo $method; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</div>