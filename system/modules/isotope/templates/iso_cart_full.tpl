<div class="iso_cart_full block <?php echo $this->class; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<?php if($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if(!sizeof($this->products)): ?>
	<div class="noItems"><?php echo $this->noItemsInCart; ?></div>
<?php else: ?>
<form action="<?php echo $this->cartJumpTo; ?>" method="post" name="cart_full">
<input type="hidden" name="action" value="cart_quantity_update"  />
<table class="productWrapper" cellpadding="0" cellspacing="0" border="0"><tbody>
<?php foreach($this->products as $product): ?>
		<!-- BEGIN PRODUCT-->
        <tr class="product">
        	<td class="col_1 removeButton">
        		<a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>">Remove</a>
        	</td>
   			<td class="col_2 productImg">
   				<a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['image'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="thumbnail" /></a>
   			</td>
       		<td class="col_3 productInfo">
       				<h3 class="productName"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['name']; ?></a></h3>
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
       		</td>
       		<td class="col_4 productQty">
       			<span class="price"><?php echo $product['price']; ?></span> x <input name="product_qty_<?php echo $product['cart_item_id']; ?>" class="qty" size="3" type="text" value="<?php echo $product['quantity']; ?>" maxlength="3" />
       		</td>
        	<td class="col_5 productTotals">                 
                   <div class="total">= <span class="total"><?php echo $product['total_price']; ?></span></div>
            </td>       
		</tr>
        <!-- END PRODUCT-->  
	<?php endforeach; ?>
	</tbody></table>
    <div class="finalPrices">
    	<!--<div class="subTotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>-->
    	<!--<div class="tax"><span class="label"><?php echo $this->taxLabel; ?></span> <?php echo $this->taxTotal; ?></div>-->
    	<div class="grandTotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>
    	<div class="clearBoth"></div>
    </div>
	<div class="cartButtons">
		<div class="update"><input type="submit" class="submit update button" value="Update Cart" /></div>
		<div class="checkout"><a class="button_large" href="<?php echo $this->checkoutJumpTo; ?>"><img src="system/modules/isotope/html/button_checkoutLg.gif" alt="Proceed to Checkout" border="0" /></a></div>
	</div>
	<div class="clearBoth"></div>
	</form>
<?php endif; ?>
	
</div>