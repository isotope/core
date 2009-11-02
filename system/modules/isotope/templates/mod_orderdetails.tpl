
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<div class="meta">
Bestellung vom: <?php echo $this->datim; ?><br />
</div>

<div class="billing">
<h2>Rechnungsadresse</h2>
<?php echo nl2br($this->billing_address); ?>
</div>

<div class="shipping">
<h2>Lieferadresse</h2>
<?php echo nl2br($this->shipping_address); ?>
</div>

<h2>Bestellungsübersicht</h2>
<table cellspacing="0" cellpadding="0" summary="Order items">
	<thead>
		<tr>
			<th>Pos.</th>
			<th>Artikelnummer</th>
			<th>Name</th>
			<th>Anzahl</th>
			<th>Einzelpreis</th>
			<th>Gesamtpreis</th>
		</tr>
	</thead>
	<tfoot>
		<tr class="subTotal">
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
		<tr class="shippingTotal">
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
		<tr class="grandTotal">
			<td colspan="4">Gesamtsumme</td>
			<td>&nbsp;</td>
			<td><?php echo $this->grandTotal; ?></td>
		</tr>
	</tfoot>
	<tbody>
<?php foreach( $this->items as $i => $item ): ?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><?php echo $item['sku']; ?></td>
			<td><?php if (strlen($item['href'])): ?><a href="<?php echo $item['href']; ?>"><?php endif; echo $item['name']; if (strlen($item['href'])): ?></a><?php endif; ?></td>
			<td><?php echo $item['quantity']; ?></td>
			<td><?php echo $item['price']; ?></td>
			<td><?php echo $item['total']; ?></td>
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