
<div class="order_review">
<h2><?php echo $this->headline; ?></h2>
<p><?php echo $this->message; ?></p>

<?php foreach( $this->info as $type => $data ): ?>
<div class="info_container <?php echo $type . $data['class']; ?>">
	<h3><?php echo $data['headline']; ?></h3>
	<div class="info"><?php echo $data['info']; ?></div>
</div>
<?php endforeach; ?>
<div class="clear">&nbsp;</div>

<table cellpadding="0" cellspacing="0" summary="Shopping Cart">
<tfoot>
	<tr class="subtotal foot_first">
		<td class="col_first">&nbsp;</td>
		<td class="name" colspan="2"><?php echo $this->subTotalLabel; ?></td>
		<td class="price">&nbsp;</td>
		<td class="price total"><?php echo $this->subTotalPrice; ?></td>
		<td class="col_last tax">&nbsp;</td>
	</tr>
<?php foreach( $this->surcharges as $surcharge ): ?>
	<tr class="<?php echo $surcharge['rowclass']; ?>">
		<td class="col_first">&nbsp;</td>
		<td class="name" colspan="2"><?php echo $surcharge['label']; ?></td>
		<td class="price"><?php echo $surcharge['price']; ?></td>
		<td class="price total"><?php echo $surcharge['total_price']; ?></td>
		<td class="col_last tax"><?php echo $surcharge['tax_id']; ?></td>
	</tr>
<?php endforeach; ?>
	<tr class="grandtotal foot_last">
		<td class="col_first">&nbsp;</td>
		<td class="name" colspan="2"><?php echo $this->grandTotalLabel; ?></td>
		<td class="price total" colspan="2"><?php echo $this->grandTotalPrice; ?></td>
		<td class="col_last tax">&nbsp;</td>
	</tr>
</tfoot>
<tbody>
<?php foreach($this->products as $product): ?>
    <tr class="<?php echo $product['class']; ?>">
		<td class="col_0 col_first image"><img src="<?php echo $product['image']['gallery']; ?>" alt="<?php echo $product['image']['alt']; ?>" class="thumbnail"<?php echo $product['image']['gallery_size']; ?> /></td>
   		<td class="col_1 name">
   			<?php echo $product['name']; ?>
			<?php if(count($product['option_values'])>0): ?>
			<div class="optionswrapper">
				<ul class="productOptions">
				<?php foreach($product['option_values'] as $option): ?>
					<li><strong><?php echo $option['name']; ?>:</strong> <?php echo implode(', ', $option['values']); ?></li>
				<!--<div class="option"><span class="optionname">OPTION:</span> PRODUCT OPTION</div>-->
			<?php endforeach; ?>
			</ul>
			</div>
			<?php endif; ?>
		</td>
		<td class="col_2 quantity"><?php echo $product['quantity']; ?> x</td>
   		<td class="col_3 price"><?php echo $product['price']; ?></td>
    	<td class="col_4 price total"><?php echo $product['total_price']; ?></td>
    	<td class="col_5 col_last tax"><?php echo $product['tax_id']; ?></td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if ($this->checkoutForm): ?><div class="payment_form"><?php echo $this->checkoutForm; ?></div><?php endif; ?>
</div>

