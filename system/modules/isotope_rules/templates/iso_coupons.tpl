<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<h2><?php echo $this->headline; ?></h2>
<p><?php echo $this->message; ?></p>
<div class="widgetContainer">
<label for="coupons"><?php echo $this->label; ?></label>
<input type="text" name="code" id="ctrl_code" value="" class="tl_text" /> <input type="submit" name="apply" id="ctrl_apply" value="<?php echo $this->sLabel; ?>" />
</div>
<?php if (strlen($this->error)): ?>
<p class="error"><?php echo $this->error; ?></p><?php endif; ?>
</div>
</form>