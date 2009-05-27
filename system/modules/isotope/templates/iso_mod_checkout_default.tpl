<div class="mod_checkout">
<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" name="frmCheckout" method="post">
	<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
	<ol class="co_wrapper">
		<?php foreach($this->checkoutSteps as $step): ?>
		<li class="step"><div><h2 class="toggler"><?php echo $step['headline']; ?></h2> <?php if($step['editEnabled']): ?> <!--<img src="tl_files/isotope/edit.jpg" border="0" class="edit" alt="edit" />--><?php endif; ?></div>
			<?php if($step['useFieldset']): ?><fieldset class="accordion"><?php endif;?>
				<h3><?php echo $step['prompt']; ?></h3>
				<?php echo $step['fields']; ?>
				<div class="clearBoth"></div>
				<!--<div class="button"><a href="#" class="continue"><img src="tl_files/isotope/continue.jpg" border="0" alt="continue" /></a></div>-->
				<div class="clearBoth"></div>
			<?php if($step['useFieldset']): ?></fieldset><?php endif;?>
			<div class="clearBoth"></div>
		</li>
		<?php endforeach; ?>
	</ol>
	<?php if ($this->showPrevious || $this->showNext): ?>
	<div class="submit_container">
		<?php if ($this->showPrevious): ?><input type="submit" class="submit previous" name="previousStep" value="<?php echo $this->previousLabel; ?>" /><?php endif; if ($this->showNext): ?>
		<input type="submit" class="submit next" name="nextStep" value="<?php echo $this->nextLabel; ?>" /><?php endif; ?>
	</div>
	<?php endif; ?>
	</form>
	
	<?php if (strlen($this->checkoutForm)): ?><div class="checkout_form"><?php echo $this->checkoutForm; ?></div><?php endif; ?>
	
</div>