<?php foreach( $this->products as $objProduct ): ?>
<?php echo $objProduct->name; ?><?php $options = $objProduct->getOptions(); if(is_array($options) && count($options)): ?> (<?php foreach($options as $option): ?><?php echo ($i==0 ? '' : ',').$option['name']; ?>: <?php echo implode(', ', $option['values']); ?><?php endforeach; ?>)<?php endif; ?>: <?php echo $objProduct->quantity_requested; ?> x <?php echo $objProduct->formatted_price; ?> = <?php echo $objProduct->formatted_total_price; ?>

<?php endforeach; ?>
--------------------
<?php echo $this->subTotalLabel; ?>: <?php echo $this->subTotalPrice; ?>

<?php foreach( $this->surcharges as $surcharge ): ?>
<?php echo $surcharge['label']; ?>: <?php echo $surcharge['price']; ?> = <?php echo $surcharge['total_price']; ?>

<?php endforeach; ?>
<?php echo $this->grandTotalLabel; ?>: <?php echo $this->grandTotalPrice; ?>