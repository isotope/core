<div class="mod_checkout block <?php echo $this->class; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>">
<?php if ($this->showForm): ?>
<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" name="frmCheckout" method="post">
	<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" /><?php endif; ?>
		<?php foreach($this->checkoutSteps as $step): ?>
		<div class="step"><h1><?php echo $step['headline']; ?></h1> <?php if($step['editEnabled']): ?> <!--<img src="tl_files/isotope/edit.jpg" border="0" class="edit" alt="edit" />--><?php endif; ?>
			<?php if($step['useFieldset']): ?><fieldset class="accordion"><?php endif;?>
				<h3><?php echo $step['prompt']; ?></h3>
				<?php echo $step['fields']; ?>
				<div class="clearBoth"></div>
					<?php if($step['useFieldset']): ?></fieldset><?php endif;?>
			<div class="clearBoth"></div>
		</div>
		<?php endforeach; ?>
	<?php if($this->showForm): if ($this->showPrevious || $this->showNext): ?>
	<div class="step_container">
		<?php if ($this->showPrevious): ?><input type="submit" class="submit previous button" name="previousStep" value="<?php echo $this->previousLabel; ?>" /><?php endif; if ($this->showNext): ?>
		<input type="submit" class="submit next button" name="nextStep" value="<?php echo $this->nextLabel; ?>" /><?php endif; ?>
	</div>
	<?php endif; ?>
	</form><?php endif; ?>
	
	<?php if (strlen($this->checkoutForm)): ?><div class="checkout_form"><?php echo $this->checkoutForm; ?></div><?php endif; ?>
<div class="clearBoth"></div>	
</div>