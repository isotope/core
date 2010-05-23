<!-- indexer::stop -->
<div class="iso_cart_mini block <?php echo $this->class; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<?php if($this->headline): ?>
<<?php echo $this->hl; ?>><a href="<?php echo $this->cartJumpTo; ?>" title="<?php echo $this->headline; ?>"><?php echo $this->headline; ?></a></<?php echo $this->hl; ?>>
<?php endif; ?>

<div class="productWrapper">
<?php if(count($this->products)): ?>
<?php foreach($this->products as $product): ?>
	<div class="product">
		<div class="removeButton"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>"><?php echo $product['remove_link_text']; ?></a></div>
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
	<div class="clear">&nbsp;</div>
	</div>
<?php endforeach; ?>
    <div class="subtotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>	
    <div class="cart"><a class="button" href="<?php echo $this->cartJumpTo; ?>"><span><?php echo $this->cartLabel; ?></span></a></div>
	<div class="checkout"><a class="button dark" href="<?php echo $this->checkoutJumpTo; ?>"><?php echo $this->checkoutJumpToLabel; ?></a></div>
<?php else: ?>
<div class="empty"><?php echo $this->message; ?></div>
<div class="clear">&nbsp;</div>	
<?php endif; ?>
</div>



</div>
<!-- indexer::start -->