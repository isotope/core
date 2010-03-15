<h3><a href="<?php echo $this->href_reader; ?>"><?php echo $this->name; ?></a></h3>

<?php if ($this->hasImage): ?>
<div class="image_container main_image"><a href="<?php echo $this->mainImage['large']; ?>" title="<?php echo $this->mainImage['desc']; ?>" rel="lightbox"><img src="<?php echo $this->mainImage['thumbnail']; ?>" alt="<?php echo $this->mainImage['alt']; ?>"<?php echo $this->mainImage['thumbnail_size']; ?> /></a></div>
<?php endif; ?>

<?php if ($this->sku): ?>
<div class="sku"><?php echo $this->sku; ?></div><?php endif; if ($this->teaser): ?>
<div class="teaser"><?php echo $this->teaser; ?></div><?php endif; ?>

<div class="price"><?php echo ($this->raw['high_price'] > $this->raw['low_price'] ? $this->priceRangeLabel . ' ' . $this->low_price : $this->price); ?></div>
<div class="details"><a href="<?php echo $this->href_reader; ?>"><?php echo $this->label_detail; ?></a></div>

<?php if($this->buttons): ?>
<div class="submit_container">
<?php if ($this->variant_widget): ?>
<div class="variants">
<input type="hidden" name="variant_options" value="<?php echo $this->variantList; ?>" />
<label for="<?php echo $this->variant_widget['name']; ?>"><?php echo $this->variant_widget['description']; ?> </label><?php echo $this->variant_widget['html']; ?>
</div>
<?php endif; ?>
<?php if($this->options): ?>
<input type="hidden" name="product_options" value="<?php echo $this->optionList; ?>" />
<div class="options">
<?php echo implode("\n", $this->options); ?>
</div>
<?php endif; ?>
<?php if ($this->useQuantity): ?>
<div class="quantity_container">
<label for="quantity_requested"><?php echo $this->quantityLabel; ?>:</label> <input type="text" class="text quantity_requested" name="quantity_requested" value="1" size="3" onblur="if (this.value=='') { this.value='1'; }" onfocus="if (this.value=='1') { this.value=''; }" />
</div>
<?php endif; ?>
<?php foreach( $this->buttons as $name => $button ): ?>
	<button type="submit" class="submit <?php echo $name; ?>" id="<?php echo $name . '_' . $this->raw['id']; ?>" name="<?php echo $name; ?>[<?php echo $this->raw['id']; ?>]" value="<?php echo $this->raw['id']; ?>"><?php echo $button['label']; ?></button>
<?php endforeach; ?>
</div>
<?php endif; ?>