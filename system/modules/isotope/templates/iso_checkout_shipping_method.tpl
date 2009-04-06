<div>
	<?php if($this->noShippingMethod): ?>
		<span><?php echo $this->noShippingMethod; ?></span>
	<?php else: ?>
		<?php foreach($this->shippingMethods as $method): ?>
			<span><h2><?php echo $method['title']; ?>: </span> <span><?php echo $method['cost']; ?></h2></span>		
		<?php endforeach; ?>
	<?php endif; ?>
</div>