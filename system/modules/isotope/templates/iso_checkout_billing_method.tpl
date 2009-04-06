<div>
	<?php if($this->noPaymentMethod): ?>
		<span><?php echo $this->noPaymentMethod; ?></span>
	<?php else: ?>
		<?php foreach($this->paymentMethods as $method): ?>
			<h2><?php echo $method['title']; ?>: </h2>
			<div>
				<?php echo $method['paymentFields']; ?>
			</div>	
		<?php endforeach; ?>
	<?php endif; ?>
</div>