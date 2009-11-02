
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

Bestellung vom: <?php echo $this->date; ?><br />

<table cellspacing="0" cellpadding="0" summary="Order items">
	<thead>
		<tr>
			<th>#</th>
			<th>Artikelnummer</th>
			<th>Name</th>
			<th>Anzahl</th>
			<th>Einzelpreis</th>
			<th>Gesamtpreis</th>
		</tr>
	</thead>
	<tbody>
<?php foreach( $this->items as $i => $item ): ?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><?php echo $item['sku']; ?></td>
			<td><?php echo $item['name']; ?></td>
			<td><?php echo $item['quantity']; ?></td>
			<td><?php echo $item['price']; ?></td>
			<td><?php echo $item['total']; ?></td>
		</tr>
<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4">Zwischensumme</td>
			<td>&nbsp;</td>
			<td><?php echo $this->subTotal; ?></td>
		</tr>
<!--
		<tr>
			<td colspan="4">Admin. Gebühren</td>
			<td>2.5 %</td>
			<td>CHF 3.15</td>
		</tr>
-->
		<tr>
			<td colspan="4">Lieferkosten</td>
			<td>&nbsp;</td>
			<td><?php echo $this->shippingTotal; ?></td>
		</tr>
<!--
		<tr>
			<td colspan="4">Enthaltene Steuern</td>
			<td>7.6 %</td>
			<td><?php echo $this->taxTotal; ?></td>
		</tr>
-->
		<tr>
			<td colspan="4">Gesamtsumme</td>
			<td>&nbsp;</td>
			<td><?php echo $this->grandTotal; ?></td>
		</tr>
	</tfoot>
</table>

</div>
<!-- indexer::continue -->