<?php foreach( $this->products as $objProduct ): ?>
<?php echo $objProduct->name; ?>: <?php echo $objProduct->quantity_requested; ?> x <?php echo $objProduct->formatted_price; ?> = <?php echo $objProduct->formatted_total_price; ?>

<?php endforeach; ?>