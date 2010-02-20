<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<div id="product_list">
<form id="productForm" action="<?php echo $this->action; ?>" method="post">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formId; ?>" />

<?php foreach( $this->products as $product ): ?>
<?php if($product['clear']): ?>
<div class="clear">&nbsp;</div>
<?php endif; ?>
<div class="<?php echo $product['class']; ?>">
<div class="formbody">
<?php echo $product['html']; ?>
</div>
<div class="clear"></div>
</div>
<?php endforeach; ?>

<?php echo $this->pagination; ?>
</form>
</div>
<?php if($this->script): ?>
<?php echo $this->script; ?>
<?php endif; ?>
</div>