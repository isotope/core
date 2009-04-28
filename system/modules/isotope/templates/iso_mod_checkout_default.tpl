<script type="text/javascript">
	<!--
		var state = 'hidden';
		
		function showhide(layer_ref) {
		
		
		
		if (state == 'visible') {
		state = 'hidden';
		}
		else {
		state = 'visible';
		}
		if (document.all) { //IS IE 4 or 5 (or 6 beta)
		eval( "document.all." + layer_ref + ".style.visibility = state");
		}
		if (document.layers) { //IS NETSCAPE 4 or below
		document.layers[layer_ref].visibility = state;
		}
		if (document.getElementById && !document.all) {
		div = document.getElementById(layer_ref);
		div.style.visibility = state;
		}
		}
		
		function ischecked(id, destinationElement)
		{
			if(document.getElementById(id).checked)
			{
				state = 'visible';
				
			}else{
				state = 'hidden';
			}
			
			if (document.all) { //IS IE 4 or 5 (or 6 beta)
			eval( "document.all." + destinationElement + ".style.visibility = state");
			}
			if (document.layers) { //IS NETSCAPE 4 or below
			document.layers[destinationElement].visibility = state;
			}
			if (document.getElementById && !document.all) {
			div = document.getElementById(destinationElement);
			div.style.visibility = state;
			}
		
		}
	//-->
</script>
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
	<div style="float: right;"><input type="submit" name="submit" value="<?php echo $this->slabel; ?>" /></div>
	</form>
	
</div>