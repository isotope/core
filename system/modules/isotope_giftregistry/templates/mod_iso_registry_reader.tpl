
<div class="registry_full">

<div class="info">
<h1><?php echo $this->registryTitle; ?></h1>
<table cellpadding="10" cellspacing="0" summary="Gift Registry Info"><tr>
<td><strong><?php echo $this->name; ?><br /><?php echo $this->second_party_name; ?></strong></td>
<td><?php echo date("m/d/Y",$this->date); ?></td>
<td><?php echo $this->event_type; ?></td>
</tr><tr><td colspan="3"><p><?php echo $this->description; ?></p></td>
</tr></table>
</div>

<div id="product_list">

<?php foreach( $this->products as $product ): ?>
<div class="<?php echo $product['class']; ?>">
<?php echo $product['html']; ?>
<p>Quantity requested: <?php echo $product['quantity_req']; ?><br />
Quantity sold: <?php echo $product['quantity_sold']; ?><br />
Options:<br />
<?php foreach($product['options'] as $label=>$value): ?>
<?php echo $label; ?>: <?php echo $value; ?><br />
<?php endforeach; ?>
</p>
</div>
<?php endforeach; ?>
</div>

</div>