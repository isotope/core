<table class='products'>
<tr>
	<td class='name'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_order_items']; ?></td>
	<td class='quantity'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_quantity_header']; ?></td>
	<td class='price'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_price_header']; ?></td>
	<td class='subtotal'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_subtotal_header']; ?></td>
</tr>
		
<?php foreach( $this->products as $objProduct ): ?>
<tr>
	<td>
		<?php echo $objProduct->name; ?>
		<?php $options = $objProduct->getOptions(); if(is_array($options) && count($options)): ?>
		<div class="optionswrapper">
			<ul class="productOptions">
			<?php foreach($options as $option): ?>
				<li><strong><?php echo $option['name']; ?>:</strong> <?php echo implode(', ', $option['values']); ?></li>
			<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</td>
	<td><?php echo $objProduct->quantity_requested; ?></td>
	<td><?php echo $objProduct->formatted_price; ?></td>
	<td><?php echo $objProduct->formatted_total_price; ?></td>
</tr>
<?php endforeach; ?>
</table>