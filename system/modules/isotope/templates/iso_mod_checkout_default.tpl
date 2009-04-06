<div class="mod_checkout">
<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post">
	<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
	<ol class="co_wrapper">
		<?php foreach($this->checkoutSteps as $step): ?>
		<li class="step"><div><h2 class="toggler"><?php echo $step['headline']; ?></h2> <?php if($step['editEnabled']): ?> <img src="tl_files/isotope/edit.jpg" border="0" class="edit" alt="edit" /><?php endif; ?></div>
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
	<div style="float: right;"><input type="submit" name="submit" value="<?php echo $this->slabel; ?>" /></div>
	</form>
</div>