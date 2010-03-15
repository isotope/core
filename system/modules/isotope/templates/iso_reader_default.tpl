<input type="hidden" id="ctrl_product_id" name="product_id" value="<?php echo $this->raw['id']; ?>" />

<h2 id="name"><?php echo $this->name; ?></h2>


<?php if ($this->hasImage): ?>
<div id="image_main" class="image_container main_image"><a href="<?php echo $this->mainImage['large']; ?>" title="<?php echo $this->mainImage['desc']; ?>" rel="lightbox"><img src="<?php echo $this->mainImage['medium']; ?>" alt="<?php echo $this->mainImage['alt']; ?>"<?php echo $this->mainImage['medium_size']; ?> /></a></div>
<?php endif; ?>
<?php if ($this->variant_widget): ?>
<div id="variants_container" class="variants">
<input type="hidden" name="variant_options" value="<?php echo $this->variantList; ?>" />
<?php if ($this->variant_widget): ?>
	<label for="<?php echo $this->variant_widget['name']; ?>"><?php echo $this->variant_widget['description']; ?> </label><?php echo $this->variant_widget['html']; ?>
<?php endif; ?>
<?php endif; ?>
</div>
<?php if($this->options): ?>
<div id="options_container" class="options">
<input type="hidden" name="product_options" value="<?php echo $this->optionList; ?>" /><div class="options">
<?php echo implode("\n", $this->options); ?>
</div>
<?php endif; ?>

<?php if (count($this->gallery)>1): ?>
<div id="image_gallery">
<?php foreach( $this->gallery as $image ): ?>
<div class="image_container gallery"><img src="<?php echo $image['gallery']; ?>" alt="<?php echo $image['alt']; ?>"<?php echo $image['gallery_size']; ?> /></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($this->sku): ?>
<div class="sku"><?php echo $this->sku; ?></div><?php endif; if ($this->description): ?>
<div class="description"><?php echo $this->description; ?></div><?php endif; ?>

<?php //if(!$this->hasVariants): ?>
<div id="ajax_price" class="price">
	<?php if($this->editablePrice): ?>
    	<label for="<?php echo $this->price['name']; ?>"><?php echo $this->price['description']; ?> </label><?php echo $this->price['html']; ?>
    <?php else: ?>
    	<?php echo $this->price; ?></div>
	<?php endif; ?>
<?php //endif; ?>

<?php if($this->buttons): ?>
<div class="submit_container">
<?php if ($this->useQuantity): ?>
<div class="quantity_container">
<label for="quantity_requested"><?php echo $this->quantityLabel; ?>:</label> <input type="text" class="text quantity_requested" name="quantity_requested" value="1" size="3" onblur="if (this.value=='') { this.value='1'; }" onfocus="if (this.value=='1') { this.value=''; }" />
</div>
<?php endif; ?>
<?php foreach( $this->buttons as $name => $button ): ?>
	<button type="submit" class="submit <?php echo $name; ?>" name="<?php echo $name; ?>" value="1"><?php echo $button['label']; ?></button>
    
<?php endforeach; ?>
</div>
<?php endif; ?>