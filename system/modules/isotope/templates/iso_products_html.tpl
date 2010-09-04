<table class='products'>
<thead>
<tr>
	<td class='name'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_order_items']; ?></td>
	<td class='quantity'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_quantity_header']; ?></td>
	<td class='price'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_price_header']; ?></td>
	<td class='subtotal'><?php echo $GLOBALS['TL_LANG']['MSC']['iso_subtotal_header']; ?></td>
</tr>
</thead>
<tfoot>
<tr class="subtotal foot_first">
	<td class="name" colspan="2"><?php echo $this->subTotalLabel; ?></td>
	<td class="price total" colspan="2"><?php echo $this->subTotalPrice; ?></td>
</tr>
<?php foreach( $this->surcharges as $surcharge ): ?>
<tr class="surcharge">
	<td class="name" colspan="2"><?php echo $surcharge['label']; ?></td>
	<td class="price"><?php echo $surcharge['price']; ?></td>
	<td class="price total"><?php echo $surcharge['total_price']; ?></td>
</tr>
<?php endforeach; ?>
<tr class="grandtotal foot_last">
	<td class="name" colspan="2"><?php echo $this->grandTotalLabel; ?></td>
	<td class="price total" colspan="2"><?php echo $this->grandTotalPrice; ?></td>
</tr>
</tfoot>
<tbody>
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