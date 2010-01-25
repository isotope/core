<?php foreach( $this->products as $objProduct ): ?>
<?php echo $objProduct->name; ?><?php if(is_array($objProduct->product_options) && count($objProduct->product_options)): ?> (<?php foreach($objProduct->product_options as $option): ?><?php echo $option['name']; ?>: <?php echo implode(', ', $option['values']); ?><?php endforeach; ?>)<?php endif; ?>: <?php echo $objProduct->quantity_requested; ?> x <?php echo $objProduct->formatted_price; ?> = <?php echo $objProduct->formatted_total_price; ?>

<?php endforeach; ?>