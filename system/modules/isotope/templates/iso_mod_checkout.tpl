<div class="mod_checkout">
	<ol class="co_wrapper">
		<?php echo foreach($this->checkoutSteps as $step): ?>
		<li class="step"><h2 class="toggler">Step 1 <img src="tl_files/isotope/edit.jpg" border="0" class="edit" alt="edit" /></h2>
			<fieldset class="accordion">
				<?php echo $step['html']; ?>
				<div class="button"><a href="#" class="continue"><img src="tl_files/isotope/continue.jpg" border="0" alt="continue" /></a></div>
				<div class="clearBoth"></div>
			</fieldset>
			<div class="clearBoth"></div>
		</li>
		<?php endforeach; ?>
	</ol>
</div>