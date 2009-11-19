<input type="hidden" name="product_id" value="<?php echo $this->raw['id']; ?>" />
<input type="hidden" name="quantity_requested" value="1" />
<h3><a href="<?php echo $this->href_reader; ?>"><?php echo $this->name; ?></a></h3>

<div class="image_container main_image">
<?php if ($this->hasImage): ?>
<a href="<?php echo $this->href_reader; ?>" title="<?php echo $this->mainImage['desc']; ?>"><img src="<?php echo $this->mainImage['thumbnail']; ?>" alt="<?php echo $this->mainImage['alt']; ?>"<?php echo $this->mainImage['thumbnail_size']; ?> /></a>
<?php else: ?>
<a href="<?php echo $this->href_reader; ?>" title="<?php echo $this->mainImage['desc']; ?>"><img src="<?php echo $this->placeholderImage; ?>" alt="<?php echo $this->name; ?>" /></a>
<?php endif; ?>
</div>

<?php if ($this->sku): ?>
<p class="sku"><?php echo $this->sku; ?></p><?php endif; if ($this->teaser): ?>
<p class="teaser"><?php echo $this->teaser; ?></p><?php endif; ?>

<p class="price<?php echo $this->use_price_override ? ' override' : ''; ?>"><?php echo $this->price; ?></p>
<p class="details"><a href="<?php echo $this->href_reader; ?>"><?php echo $this->label_detail; ?></a></p>
