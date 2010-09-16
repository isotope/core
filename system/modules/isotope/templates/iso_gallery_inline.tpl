<?php if ($this->type == 'medium' || $this->type == 'gallery'): ?>
<div class="image_container"><a title="<?php echo $this->desc; ?>" href="<?php echo $this->link ? $this->link : $this->medium; ?>" onclick="$$('#images_<?php echo $this->product_id; ?>_mediumsize img').set('src', this.href); return false"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></a></div>
<?php else: ?>
<div class="image_container"><a href="<?php echo $this->href_reader; ?>" title="<?php echo $this->desc; ?>"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></a></div>
<?php endif; ?>