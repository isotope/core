
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
</div>
</form>
<div class="clear"></div>
</div>
<?php endforeach; ?>

<?php echo $this->pagination; ?>

</div>