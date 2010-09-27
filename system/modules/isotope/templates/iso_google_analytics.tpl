pageTracker._addTrans(
   '<?php echo $this->id; ?>',
   '<?php echo $this->storeName; ?>',
   '<?php echo $this->grandTotal; ?>', 
   '<?php echo $this->tax; ?>',
   '<?php echo $this->shipping; ?>',
   '<?php echo $this->city; ?>',
   '<?php echo $this->state; ?>',
   '<?php echo $this->country; ?>'
);
<?php foreach($this->item as $item): ?>
pageTracker._addItem(
   '<?php echo $this->id; ?>',			//Order ID, not product id!
   '<?php echo $item['sku']; ?>',
   '<?php echo $item['name']; ?>',
   '<?php echo $item['variant']; ?>',
   '<?php echo $item['price']; ?>',
   '<?php echo $item['quantity']; ?>'
);
<?php endforeach; ?>
pageTracker._trackTrans();