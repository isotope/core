<!-- indexer::stop -->
<div class="iso_cart_mini block <?php echo $this->class; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>">

<?php if($this->headline): ?>
<<?php echo $this->hl; ?>><a href="<?php echo $this->cartJumpTo; ?>" title="<?php echo $this->headline; ?>"><?php echo $this->headline; ?></a></<?php echo $this->hl; ?>>
<?php endif; ?>


<div class="productWrapper">
<?php if(!sizeof($this->products)): ?>
<div class="empty"><?php echo $this->noItemsInCart; ?></div>
<?php else: ?>
<?php foreach($this->products as $product): ?>
<form action="<?php echo $this->action; ?>" method="post">
	<div class="product">
		<div class="removeButton"><button type="submit" name="remove" class="removeButton" value="<?php echo $product['cart_item_id']; ?>"><img src="<?php echo $this->removeImage['image']; ?>" border="0" alt="<?php echo $product['remove_link_title']; ?>"<?php echo $this->removeImage['size']; ?> /></button></div>
        <div class="productName"><?php echo $product['name']; ?></div>
		<div class="info">
        	<?php if($this->showOptions && $product['product_options']): ?>
			<div class="optionswrapper">
				<ul class="productOptions">
				<?php foreach($product['product_options'] as $option): ?>
					<li><strong><?php echo $option['name']; ?>:</strong> <?php echo implode(', ', $option['values']); ?></li>
				<!--<div class="option"><span class="optionname">OPTION:</span> PRODUCT OPTION</div>-->
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="price">Qty x <?php echo $product['quantity']; ?> = <span class="total"><?php echo $product['total_price']; ?></span></div>
		</div>
	<div class="clearBoth"></div>
	</div>
</form>
<?php endforeach; ?>
    <div class="subtotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>	
	<div class="checkout"><a class="button dark" href="<?php echo $this->checkoutJumpTo; ?>">Proceed to Checkout</a></div>
<div class="clearBoth"></div>	
<?php endif; ?>
</div>




</div>
<!-- indexer::start -->