<?php if ($this->type == 'gallery'): ?>
<div class="image_container<?php if ($this->class) echo ' '.$this->class; ?>"><a title="<?php echo $this->desc; ?>" href="<?php echo $this->link ? $this->link : $this->medium; ?>" onclick="return Isotope.inlineGallery(this, '<?php echo $this->product_id; ?>');"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?><?php if ($this->class) echo ' class="'.$this->class.'"'; ?> /></a></div>
<?php elseif ($this->type == 'medium'): ?>
<div class="image_container"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></div>
<?php else: ?>
<div class="image_container"><a href="<?php echo $this->href_reader; ?>" title="<?php echo $this->desc; ?>"><img src="<?php echo $this->{$this->type}; ?>" alt="<?php echo $this->alt; ?>"<?php echo $this->{$this->type.'_size'}; ?> /></a></div>
<?php endif; ?>