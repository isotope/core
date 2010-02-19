<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<form action="<?php echo $this->action; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formId; ?>" />
<input type="hidden" name="product_options" value="<?php echo $this->productOptionsList; ?>" />
<?php echo $this->fields; ?>
<button type="submit" name="addDonation"><?php echo $this->slabel; ?></button>
</div>
</form>

</div>