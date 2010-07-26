
<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post" enctype="<?php echo $this->enctype; ?>">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />

<h2><?php echo $this->name; ?></h2>

<?php echo $this->images->generateMainImage('medium'); ?>
<?php if($this->hasOptions): ?>
<div id="options_container" class="options">
<?php echo implode("\n", $this->options); ?>
</div>
<?php endif; ?>

<?php echo $this->images->generateGallery(); ?>

<?php if ($this->sku): ?>
<div class="sku"><?php echo $this->sku; ?></div><?php endif; if ($this->description): ?>
<div class="description"><?php echo $this->description; ?></div><?php endif; ?>
<div class="price"><?php echo $this->price; ?></div>
<?php if($this->prices): ?>
<div class="priceGroup">
<dl>
<?php foreach($this->prices as $k=>$v): ?>
<dd class="<?php echo $k; ?>"><?php echo $v['label'] . ': ' . $v['total_price'] .($v['price'] ? ' (Save '.$v['price'].')':''); ?></dd>
<?php endforeach; ?> 
</dl>
</div>
<?php endif; ?>
<?php if($this->buttons): ?>
<div class="submit_container">
<?php if ($this->useQuantity): ?>
<div class="quantity_container">
<label for="quantity_requested"><?php echo $this->quantityLabel; ?>:</label> <input type="text" class="text" name="quantity_requested" value="1" size="3" onblur="if (this.value=='') { this.value='1'; }" onfocus="if (this.value=='1') { this.value=''; }" />
</div>
<?php endif; ?>
<?php foreach( $this->buttons as $name => $button ): ?>
	<input type="submit" class="submit <?php echo $name; ?>" name="<?php echo $name; ?>" value="<?php echo $button['label']; ?>">
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</form>