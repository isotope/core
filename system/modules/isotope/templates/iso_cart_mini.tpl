<!-- indexer::stop -->
<div class="iso_cart_mini">

<h2 class="title"><a href="<?php echo $this->cartJumpTo; ?>">Your Cart</a></h2>

<div class="productWrapper">
<?php if(!sizeof($this->products)): ?>
<div class="noItems"><?php echo $this->noItemsInCart; ?></div>
<?php else: ?>
<?php foreach($this->products as $product): ?>
	<div class="product">
		<div class="removeButton"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>">x</a></div>
        <div class="productName"><?php echo $product['name']; ?></div>
		<div class="info">
        	<?php if(sizeof($product['options'])): ?>
				<div class="optionswrapper">	
				<?php foreach($product['options'] as $option): ?>
					<div class="option"><span class="optionname">OPTION:</span> $option['value']</div>
				<?php endforeach; ?>
                </div>
			<?php endif; ?>
			<div class="price">Qty x <?php echo $product['quantity']; ?> = <span class="total"><?php echo $product['total_price']; ?></span></div>
		</div>
	<div class="clearBoth"></div>
	</div>
<?php endforeach; ?>
    <div class="horizontalLine"></div>
    <div class="clearBoth"></div>
    <div class="subTotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>
	<div class="clearBoth"></div>
	
	<p class="checkout"><a href="<?php echo $this->checkoutJumpTo; ?>"><img src="system/modules/isotope/html/button_checkoutSm.gif" alt="Proceed to Checkout" border="0" /></a></p>
	
<?php endif; ?>
</div>



</div>
<!-- indexer::start -->