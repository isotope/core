<?php if ($this->type == 'medium'): ?>
<div class="image_container"><a href="<?php echo $this->large; ?>" title="<?php echo $this->desc; ?>" rel="lightbox"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></a></div>
<?php else: ?>
<div class="image_container"><a href="<?php echo $this->href_reader; ?>" title="<?php echo $this->desc; ?>"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></a></div>
<?php endif; ?>