<!-- indexer::stop -->
<div class="iso_cart_mini block <?php echo $this->class; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>">

<?php if($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if(!count($this->products)): ?>
<div class="empty"><?php echo $this->noItemsInCart; ?></div>
<?php else: ?>
<table class="productWrapper">
	<tfoot>
		<tr class="subtotal">
			<td colspan="2"><?php echo $this->subTotalLabel; ?></td>
			<td><?php echo $this->subTotalPrice; ?></td>
		</tr>
	</tfoot>
	<tbody>
<?php foreach($this->products as $product): ?>
		<tr class="product">
			<td class="quantity"><?php echo $product['quantity']; ?>x</td>
	        <td class="productName"><?php echo $product['name']; ?></td>
			<td class="price"><?php echo $product['total_price']; ?></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>
<a class="checkout" style="float:right" href="<?php echo $this->checkoutJumpTo; ?>"><span>Proceed to Checkout</span></a>
<a class="cart" href="<?php echo $this->cartJumpTo; ?>"><span>Cart</span></a>
<?php endif; ?>

</div>
<!-- indexer::start -->