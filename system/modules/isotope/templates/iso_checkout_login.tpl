<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<div class="ce_text leftBox block">

<p><a href="<?php echo $this->create_account_link; ?>"><?php echo $this->create_account_link_label; ?>Create Account</a></p>

</div>

<div class="rightBox">

<?php echo $this->loginModule; ?>

<div class="ce_hyperlink recoverPassword block">

<?php echo $this->forgotPassword; ?>
Forgot Your Password? <a href="recover-password.html" class="hyperlink_txt" title="Recover it here.">Recover it here.</a> 
</div>

</div>
</div>

