<input type="hidden" id="ctrl_product_id" name="product_id" value="<?php echo $this->raw['id']; ?>" />

<h2 id="product_name"><?php echo $this->name; ?></h2>


<?php if ($this->hasImage): ?>
<div id="image_main" class="image_container main_image"><a href="<?php echo $this->mainImage['large']; ?>" title="<?php echo $this->mainImage['desc']; ?>" rel="lightbox"><img src="<?php echo $this->mainImage['thumbnail']; ?>" alt="<?php echo $this->mainImage['alt']; ?>"<?php echo $this->mainImage['thumbnail_size']; ?> /></a></div>
<?php endif; ?>
<?php if ($this->variant_widget): ?>
<div class="variants">
<input type="hidden" name="variant_options" value="<?php echo $this->variantList; ?>" />
<?php if ($this->variant_widget): ?>
	<label for="<?php echo $this->variant_widget['name']; ?>"><?php echo $this->variant_widget['description']; ?> </label><?php echo $this->variant_widget['html']; ?>
<?php endif; ?>
<?php endif; ?>
</div>
<?php if($this->options): ?>
<div class="options">
<input type="hidden" name="product_options" value="<?php echo $this->optionList; ?>" /><div class="options">
<?php foreach($this->options as $option): ?>
	<label for="<?php echo $option['name']; ?>"><?php echo $option['description']; ?> </label><?php echo $option['html']; ?>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($this->hasGallery): ?>
<div id="image_gallery">
<?php foreach( $this->gallery as $image ): ?>
<div class="image_container gallery"><a href="<?php echo $image['large']; ?>" title="<?php echo $image['desc']; ?>" rel="lightbox"><img src="<?php echo $image['gallery']; ?>" alt="<?php echo $image['alt']; ?>"<?php echo $image['gallery_size']; ?> /></a></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($this->sku): ?>
<div class="sku"><?php echo $this->sku; ?></div><?php endif; if ($this->description): ?>
<div class="description"><?php echo $this->description; ?></div><?php endif; ?>

<?php //if(!$this->hasVariants): ?>
<div id="product_price" class="price">
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