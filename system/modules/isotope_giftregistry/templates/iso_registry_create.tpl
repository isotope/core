
<div class="registry_create">

<?php if($this->message): ?>
<p><?php echo $this->message; ?></p>
<?php endif; ?>

<form action="<?php echo $this->action; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formId; ?>" />
<?php echo $this->fields; ?>
<button type="submit" name="createRegistry"><?php echo $this->slabel; ?></button>
</div>
</form>

</div>