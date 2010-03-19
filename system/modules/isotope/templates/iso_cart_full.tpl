
<div class="cart_full">

<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<table cellpadding="0" cellspacing="0" summary="Shopping Cart">
<tfoot>
	<tr class="subtotal foot_first foot_last">
		<td class="col_first">&nbsp;</td>
		<td class="name" colspan="2"><?php echo $this->subTotalLabel; ?></td>
		<td class="price" colspan="2"><?php echo $this->subTotalPrice; ?></td>
		<td class="col_last">&nbsp;</td>
	</tr>
	
</tfoot>
<tbody>
<?php if(count($this->products)): ?>
<?php foreach($this->products as $product): ?>
    <tr class="<?php echo $product['class']; ?>">
		<td class="col_0 col_first image"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['image']['gallery']; ?>" alt="<?php echo $product['image']['alt']; ?>" class="thumbnail"<?php echo $product['image']['gallery_size']; ?> /></a></td>
   		<td class="col_1 name">
   			<a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['name']; ?></a>
			<?php if($product['product_options']): ?>
			<div class="optionswrapper">
				<ul class="productOptions">
				<?php foreach($product['product_options'] as $option): ?>
					<li><strong><?php echo $option['name']; ?>:</strong> <?php echo implode(', ', $option['values']); ?></li>
				<!--<div class="option"><span class="optionname">OPTION:</span> PRODUCT OPTION</div>-->
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</td>
		<td class="col_2 quantity"><input name="quantity[<?php echo $product['cart_item_id']; ?>]" size="3" type="text" class="text" value="<?php echo $product['quantity']; ?>" maxlength="3" /></td>
   		<td class="col_3 price"><?php echo $product['price']; ?></td>
    	<td class="col_4 price total"><?php echo $product['total_price']; ?></td>
    	<td class="col_5 col_last remove"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>" class="remove"><?php echo $product['remove_link_text']; ?></a></td>
	</tr>
<?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="submit_container">
	<button type="submit" class="submit update"><span><?php echo $GLOBALS['TL_LANG']['MSC']['updateCartBT']; ?></span></button>
	<a class="continue" href="<?php echo $this->continueShoppingLink; ?>"><span><?php echo $this->continueShoppingLabel; ?></span></a>
	<a class="checkout" href="<?php echo $this->checkoutJumpTo; ?>"><span><?php echo $this->checkoutJumpToLabel; ?></span></a>
</div>
<?php else: ?>
<tr>
	<td colspan="4" class="empty"><?php echo $this->message; ?></td>
</tr>
<tr>
	<td colspan="4"><a class="continue" href="<?php echo $this->continueShoppingLink; ?>"><span><?php echo $this->continueShoppingLabel; ?></span></a></td>
</tr>
</tbody>
</table>
<?php endif; ?>
</form>
</div>