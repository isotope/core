
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if (is_array($this->steps) && count($this->steps)): ?>
<div class="steps block">
<ul class="steps<?php echo count($this->steps); ?>">
<?php foreach( $this->steps as $step ): ?>
	<li class="<?php echo $step['class']; ?>"><?php if (strlen($step['href'])): ?><a href="<?php echo $step['href']; ?>" title="<?php echo $step['title']; ?>"><?php endif; echo $step['label']; if(strlen($step['href'])): ?></a><?php endif; ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if (strlen($this->message)): ?>
<p class="<?php echo $this->mtype; ?> message"><?php echo $this->message; ?></p>
<?php endif; ?>

<?php if ($this->showForm): ?>
<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post" enctype="<?php echo $this->enctype; ?>">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<?php echo $this->hidden; ?>
<?php echo $this->fields; ?>
</div>
<div class="clear">&nbsp;</div>
<?php if ($this->showPrevious || $this->showNext): ?>
	<div class="submit_container">
		<?php if ($this->showPrevious): ?><button type="submit" class="submit previous button" name="previousStep" value="1"><?php echo $this->previousLabel; ?></button><?php endif; if ($this->showNext): ?>
		<button type="submit" class="submit <?php echo $this->nextClass; ?> button" name="nextStep" value="1"><?php echo $this->nextLabel; ?></button><?php endif; ?>
	</div>
<?php endif; ?>
</form>
<?php else: ?>
<?php echo $this->fields; ?>
<?php endif; ?>

<?php if (strlen($this->checkoutForm)): ?><div class="checkout_form"><?php echo $this->checkoutForm; ?></div><?php endif; ?>

</div>
<!-- indexer::continue -->