
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<div class="meta">
Bestellung vom: <?php echo $this->datim; ?><br />
</div>

<?php foreach( $this->info as $type => $data ): ?>
<div class="info_container <?php echo $type . $data['class']; ?>">
	<h3><?php echo $data['headline']; ?></h3>
	<div class="info"><?php echo $data['info']; ?></div>
</div>
<?php endforeach; ?>
<div class="clear">&nbsp;</div>

<table cellspacing="0" cellpadding="0" summary="Order items">
	<tfoot>
		<tr class="subtotal foot_first">
			<td class="col_first">&nbsp;</td>
			<td class="name" colspan="2"><?php echo $this->subTotalLabel; ?></td>
			<td class="price">&nbsp;</td>
			<td class="price total"><?php echo $this->subTotalPrice; ?></td>
			<td class="col_last tax">&nbsp;</td>
		</tr>
<?php if (is_array($this->surcharges)): foreach( $this->surcharges as $surcharge ): ?>
		<tr>
			<td class="col_first">&nbsp;</td>
			<td class="name" colspan="2"><?php echo $surcharge['label']; ?></td>
			<td class="price"><?php echo $surcharge['price']; ?></td>
			<td class="price total"><?php echo $surcharge['total_price']; ?></td>
			<td class="col_last tax"><?php echo $surcharge['tax_id']; ?></td>
		</tr>
<?php endforeach; endif; ?>
		<tr class="grandtotal foot_last">
			<td class="col_first">&nbsp;</td>
			<td class="name" colspan="2">Gesamtsumme</td>
			<td class="price total" colspan="2"><?php echo $this->grandTotal; ?></td>
			<td class="col_last tax">&nbsp;</td>
		</tr>
	</tfoot>
	<tbody>
<?php foreach( $this->items as $item ): ?>
		<tr>
			<td class="col_0 col_first"><?php echo $item['sku']; ?></td>
			<td class="col_1 name"><?php if (strlen($item['href'])): ?><a href="<?php echo $item['href']; ?>"><?php endif; echo $item['name']; if (strlen($item['href'])): ?></a><?php endif; ?></td>
			<td class="col_2 quantity"><?php echo $item['quantity']; ?></td>
			<td class="col_3 price"><?php echo $item['price']; ?></td>
			<td class="col_4 price total"><?php echo $item['total']; ?></td>
	    	<td class="col_5 col_last tax"><?php echo $product['tax_id']; ?></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>


<?php if (count($this->downloads)): ?>
<h2>Verfügbare Downloads</h2>
<?php foreach( $this->downloads as $download ): ?>
<div class="download"><?php if ($download['downloadable']): ?><a href="<?php echo $download['href']; ?>" /><?php endif; echo $download['title']; if ($download['downloadable']): ?></a><?php endif; echo $download['remaining']; ?></div>
<?php endforeach; endif; ?>

</div>
<!-- indexer::continue -->