<div class="iso_cart_full">

<?php if(!sizeof($this->products)): ?>
	<div class="noItems"><?php echo $this->noItemsInCart; ?></div>
<?php else: ?>
<div class="productWrapper">

<form action="<?php echo $this->cartJumpTo; ?>" method="post" name="cart_full">
<input type="hidden" name="form_action" value="cart_update"  />

<?php foreach($this->products as $product): ?>
		<!-- BEGIN PRODUCT-->
        <div class="product">
        	<div class="col removeButton"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>">x</a> Remove</div>
   			<div class="col productImg"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['product_name']; ?>"><img src="<?php echo $product['image'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="thumbnail" /></a></div>
       		<div class="col productInfo">
       				<h3 class="productName"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['product_name']; ?>"><?php echo $product['name']; ?></a></h3>
       				<div class="optionswrapper">
       					<?php if(sizeof($product['option_values'])>0): ?>
       					<ul class="productOptions">
       					<?php foreach($product['option_values'] as $option): ?>
       						<li><strong><?php echo $option['name']; ?>:</strong> <?php echo join(', ', $option['values']); ?></li>
							<!--<div class="option"><span class="optionname">OPTION:</span> PRODUCT OPTION</div>-->
						<?php endforeach; ?>
						</ul>
						<?php endif; ?>
       				</div>
       		</div>
       		<div class="col productQty">
       			<span class="price"><?php echo $product['price']; ?></span> x <input name="product_qty_<?php echo $product['cart_item_id']; ?>" class="qty" size="3" type="text" value="<?php echo $product['quantity']; ?>" maxlength="3" />
       		</div>
        	<div class="col productTotals">                 
                   <div class="total"><span class="total"><?php echo $product['total_price']; ?></span></div>
            </div>       
            <div class="clearBoth"></div>
		</div>
        <!-- END PRODUCT-->
        <div class="divider"></div>   
	<?php endforeach; ?>
    <div class="horizontalLine"></div>
    <div class="clearBoth"></div>
    <div class="finalPrices">
    	<div class="subTotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>
    	<div class="tax"><span class="label"><?php echo $this->taxLabel; ?></span> <?php echo $this->taxTotal; ?></div>
    	<div class="grandTotal"><span class="label"><?php echo $this->grandTotalLabel; ?></span> <?php echo $this->grandTotalPrice; ?></div>
    	<div class="clearBoth"></div>
    </div>

	</div>
	<div class="cartButtons">
		<div class="update"><a class="button_small" href="javascript:document.cart_full.submit();" onclick="javascript:document.cart_full.submit();"><img src="system/modules/isotope/html/button_update.gif" alt="Update Cart" border="0" /></a></div>
		<div class="checkout"><a class="button_large" href="<?php echo $this->checkoutJumpTo; ?>"><img src="system/modules/isotope/html/button_checkoutLg.gif" alt="Proceed to Checkout" border="0" /></a></div>
	</div>
	
	</form>
<?php endif; ?>
	
</div>