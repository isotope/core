
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<table cellspacing="0" cellpadding="0" summary="Shop Orders">
	<tr>
		<th>Bestellt</th>
		<th>Anzahl Artikel</th>
		<th>Gesamtpreis</th>
		<th>Status</th>
		<th>&nbsp;</th>
	</tr>
<?php foreach( $this->orders as $order ): ?>
	<tr>
		<td><?php echo $order['date']; ?></td>
		<td><?php echo $order['items']; ?></td>
		<td><?php echo $order['grandTotal']; ?></td>
		<td><?php echo $order['status']; ?></td>
		<td></td>
	</tr>
<?php endforeach; ?>
</table>

</div>
<!-- indexer::continue -->