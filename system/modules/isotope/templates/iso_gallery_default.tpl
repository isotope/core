<?php if ($this->type == 'medium' || $this->type == 'gallery'): ?>
<div class="image_container"><a title="<?php echo $this->desc; ?>"<?php if ($this->link): ?>href="<?php echo $this->link; ?>"<?php echo $this->rel ? ' rel="'.$this->rel.'"' : LINK_NEW_WINDOW; ?><?php else: ?>href="<?php echo $this->large; ?>" rel="lightbox"<?php endif; ?>><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></a></div>
<?php else: ?>
<div class="image_container"><a href="<?php echo $this->href_reader; ?>" title="<?php echo $this->desc; ?>"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></a></div>
<?php endif; ?>