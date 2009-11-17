<input type="hidden" name="product_id" value="<?php echo $this->raw['id']; ?>" />

<h3><a href="<?php echo $this->href_reader; ?>"><?php echo $this->name; ?></a></h3>

<?php if ($this->hasImage): ?>
<div class="image_container main_image"><a href="<?php echo $this->mainImage['large']; ?>" title="<?php echo $this->mainImage['desc']; ?>" rel="lightbox"><img src="<?php echo $this->mainImage['thumbnail']; ?>" alt="<?php echo $this->mainImage['alt']; ?>"<?php echo $this->mainImage['thumbnail_size']; ?> /></a></div>
<?php endif; ?>

<?php if ($this->sku): ?>
<p class="sku"><?php echo $this->sku; ?></p><?php endif; if ($this->teaser): ?>
<p class="teaser"><?php echo $this->teaser; ?></p><?php endif; ?>

<p class="price<?php echo $this->use_price_override ? ' override' : ''; ?>"><?php echo $this->price; ?></p>
