<!-- indexer::stop -->
<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="<?php echo $this->method; ?>" enctype="<?php echo $this->enctype; ?>"<?php echo $this->attributes; ?>>
	<div class="formbody">
		<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
		<?php echo $this->hidden; ?>
		<?php foreach($this->buttons as $button): ?>
           	<div class="productButton<?php echo " " . $button['buttonClass']; ?>">
           		<?php echo $button['buttonObject']; ?>
           	</div>
       	<?php endforeach; ?>
    </div>
</form>
<!-- indexer::start -->

