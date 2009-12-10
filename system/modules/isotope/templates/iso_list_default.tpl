<input type="hidden" name="product_id" value="<?php echo $this->raw['id']; ?>" />

<h3><a href="<?php echo $this->href_reader; ?>"><?php echo $this->name; ?></a></h3>

<?php if ($this->hasImage): ?>
<div class="image_container main_image"><a href="<?php echo $this->mainImage['large']; ?>" title="<?php echo $this->mainImage['desc']; ?>" rel="lightbox"><img src="<?php echo $this->mainImage['thumbnail']; ?>" alt="<?php echo $this->mainImage['alt']; ?>"<?php echo $this->mainImage['thumbnail_size']; ?> /></a></div>
<?php endif; ?>

<?php if ($this->sku): ?>
<p class="sku"><?php echo $this->sku; ?></p><?php endif; if ($this->teaser): ?>
<p class="teaser"><?php echo $this->teaser; ?></p><?php endif; ?>

<p class="price"><?php echo $this->price; ?></p>
<p class="details"><a href="<?php echo $this->href_reader; ?>"><?php echo $this->label_detail; ?></a></p>

<?php if($this->buttons): ?>
<div class="submit_container">
<?php if ($this->hasOptions): ?>
<input type="hidden" name="product_options" value="<?php echo $this->optionList; ?>" />
<input type="hidden" name="variant_options" value="<?php echo $this->variantList; ?>" />
<div class="variants">
<?php if ($this->variant_widget): ?>
	<label for="<?php echo $this->variant_widget['name']; ?>"><?php echo $this->variant_widget['description']; ?> </label><?php echo $this->variant_widget['html']; ?>
<?php endif; ?>
</div>
<div class="options">
<?php foreach($this->options as $option): ?>
	<?php echo $option['html']; ?>
<?php endforeach; ?>
</div>
<?php endif; ?>
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