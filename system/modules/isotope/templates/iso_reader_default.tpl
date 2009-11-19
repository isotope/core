<input type="hidden" name="product_id" value="<?php echo $this->raw['id']; ?>" />

<h2><?php echo $this->name; ?></h2>

<div class="image_container main_image">
<?php if ($this->hasImage): ?>
<a href="<?php echo $this->mainImage['large']; ?>" title="<?php echo $this->mainImage['desc']; ?>" rel="lightbox"><img src="<?php echo $this->mainImage['thumbnail']; ?>" alt="<?php echo $this->mainImage['alt']; ?>"<?php echo $this->mainImage['thumbnail_size']; ?> /></a>
<?php else: ?>
<img src="<?php echo $this->placeholderImage; ?>" alt="<?php echo $this->name; ?>" />
<?php endif; ?>
</div>

<?php if ($this->hasGallery): foreach( $this->gallery as $image ): ?>
<div class="image_container gallery"><a href="<?php echo $image['large']; ?>" title="<?php echo $image['desc']; ?>" rel="lightbox"><img src="<?php echo $image['gallery']; ?>" alt="<?php echo $image['alt']; ?>"<?php echo $image['gallery_size']; ?> /></a></div>
<?php endforeach; endif; ?>

<?php if ($this->sku): ?>
<p class="sku"><?php echo $this->sku; ?></p><?php endif; if ($this->description): ?>
<p class="description"><?php echo $this->description; ?></p><?php endif; ?>

<p class="price<?php echo $this->use_price_override ? ' override' : ''; ?>"><?php echo $this->price; ?></p>
