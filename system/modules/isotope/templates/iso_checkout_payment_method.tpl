<div class="payment_method">
<h2><?php echo $this->headline; ?></h2>
<p><?php echo $this->message; ?></p>
<ul>
<?php foreach($this->paymentMethods as $method): ?>
	<li><?php echo $method; ?></li>
<?php endforeach; ?>
</ul><?php if (strlen($this->error)): ?>
<p class="error"><?php echo $this->error; ?></p><?php endif; ?>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function() {
	$$('.payment_data').setStyle('display', 'none');
	$$('input.payment_module').each( function(el) {
		el.addEvent('click', function (event) {
			$$('.payment_data').setStyle('display', 'none');
			if ($(('payment_data_'+event.target.value)))
				$(('payment_data_'+event.target.value)).setStyle('display', 'block');
		});
		if (el.checked && $(('payment_data_'+el.value))) {
			$(('payment_data_'+el.value)).setStyle('display', 'block');
		}
	});
});
//--><!]]>
</script>
</div>