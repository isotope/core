
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<div class="meta">
Bestellung vom: <?php echo $this->datim; ?><br />
</div>

<div class="billing">
<h2><?php echo $this->billing_label; ?></h2>
<?php echo $this->billing_address; ?>
</div>

<?php if ($this->has_shipping): ?>
<div class="shipping">
<h2><?php echo $this->shipping_label; ?></h2>
<?php echo $this->shipping_address; ?>
</div>
<?php endif; ?>

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
<?php if (is_array($this->surcharges)): foreach( $this->surcharges as $surcharge ): ?>
		<tr>
			<td colspan="4"><?php echo $surcharge['label']; ?></td>
			<td><?php echo $surcharge['price']; ?></td>
			<td><?php echo $surcharge['total_price']; ?></td>
		</tr>
<?php endforeach; endif; ?>
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