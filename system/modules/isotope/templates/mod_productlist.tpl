
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php foreach( $this->products as $product ): ?>
<?php if($product['clear']): ?>
<div class="clear">&nbsp;</div>
<?php endif; ?>
<div class="<?php echo $product['class']; ?>">
<form action="<?php echo $this->action; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formId; ?>" />
<?php echo $product['html']; ?>
<?php if($this->buttons): ?>
<div class="submit_container">
<?php foreach( $this->buttons as $name => $button ): ?>
	<button type="submit" name="<?php echo $name; ?>" value="1"><?php echo $button['label']; ?></button>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</form>
<div class="clear"></div>
</div>
<?php endforeach; ?>

<?php echo $this->pagination; ?>

</div>