<!-- indexer::stop -->
<div class="iso_cart_mini block <?php echo $this->class; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>">

<?php if($this->headline): ?>
<<?php echo $this->hl; ?>><a href="<?php echo $this->cartJumpTo; ?>" title="<?php echo $this->headline; ?>"><?php echo $this->headline; ?></a></<?php echo $this->hl; ?>>
<?php endif; ?>

<div class="productWrapper">
<?php if(!sizeof($this->products)): ?>
<div class="noItems"><?php echo $this->noItemsInCart; ?></div>
<?php else: ?>
<?php foreach($this->products as $product): ?>
	<div class="product">
		<div class="removeButton"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>">Remove</a></div>
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
    <div class="subtotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>	
	<div class="checkout"><a class="button dark" href="<?php echo $this->checkoutJumpTo; ?>">Proceed to Checkout</a></div>
<div class="clearBoth"></div>	
<?php endif; ?>
</div>



</div>
<!-- indexer::start -->