
<div class="cart_full">

<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<table cellpadding="0" cellspacing="0" summary="Shopping Cart">
<tfoot>
	<tr class="subtotal foot_first">
		<td class="col_first">&nbsp;</td>
		<td class="name" colspan="2"><?php echo $this->subTotalLabel; ?></td>
		<td class="price">&nbsp;</td>
		<td class="price total"><?php echo $this->subTotalPrice; ?></td>
		<td class="tax">&nbsp;</td>
		<td class="col_last remove">&nbsp;</td>
	</tr>
<?php foreach( $this->surcharges as $surcharge ): ?>
	<tr class="<?php echo $surcharge['rowclass']; ?>">
		<td class="col_first">&nbsp;</td>
		<td class="name" colspan="2"><?php echo $surcharge['label']; ?></td>
		<td class="price"><?php echo $surcharge['price']; ?></td>
		<td class="price total"><?php echo $surcharge['total_price']; ?></td>
		<td class="tax"><?php echo $surcharge['tax_id']; ?></td>
		<td class="col_last remove">&nbsp;</td>
	</tr>
<?php endforeach; ?>
	<tr class="grandtotal foot_last">
		<td class="col_first">&nbsp;</td>
		<td class="name" colspan="2"><?php echo $this->grandTotalLabel; ?></td>
		<td class="price total" colspan="2"><?php echo $this->grandTotalPrice; ?></td>
		<td class="tax">&nbsp;</td>
		<td class="col_last remove">&nbsp;</td>
	</tr>
</tfoot>
<tbody>
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
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</td>
		<td class="col_2 quantity"><input name="quantity[<?php echo $product['cart_item_id']; ?>]" size="3" type="text" class="text" value="<?php echo $product['quantity']; ?>" maxlength="3" /></td>
   		<td class="col_3 price"><?php echo (!count($product['coupons']) ? $product['original_price'] : '<s>'.$product['original_price'].'</s><br />'.$product['price']); ?></td>
    	<td class="col_4 price total"><?php echo $product['total_price']; ?></td>
    	<td class="col_5 tax"><?php echo $product['tax_id']; ?></td>
    	<td class="col_6 col_last remove"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>" class="remove"><?php echo $product['remove_link_text']; ?></a></td>
	</tr>
<?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="submit_container">
	<input type="submit" class="submit update" name="update_cart" id="ctrl_update_cart" value="<?php echo $GLOBALS['TL_LANG']['MSC']['updateCartBT']; ?>" />
	<a class="checkout" href="<?php echo $this->checkoutJumpTo; ?>"><span><?php echo $this->checkoutJumpToLabel; ?></span></a>
</div>
</form>
<?php if(count($this->forms)) implode("\n", $this->forms): ?>
</div>