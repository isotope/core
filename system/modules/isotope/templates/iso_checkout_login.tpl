<div class="iso_checkout_login block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<?php if($this->allowGuestCheckout): ?>
<div class="ce_text leftBox block">
	<div class="ce_text block">
    	Prefer to check out as a guest?<br />
    	<a href="<?php echo $this->guestCheckoutUrl; ?>" class="hyperlink_txt" title="Checkout as a guest">Click Here</a>.
    </div>
</div>
<?php endif; ?>

<div class="rightBox">
<?php echo $this->loginModule; ?>
<div class="ce_hyperlink recoverPassword block">
<?php echo $this->forgotPassword; ?>
Forgot Your Password?<br /><a href="recover-password.html" class="hyperlink_txt" title="Recover it here.">Recover it here.</a> 
<?php //echo $this->forgotPasswordModule; ?>
</div>
<div class="ce_hyperlink register block">
Not a customer?<br /><a href="{{link_url::checkout-register}}" title="{{link_title::checkout-register}}">Register here.</a>
</div>
</div>
<div class="clearBoth"></div>
</div>