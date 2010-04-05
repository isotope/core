<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> iso_registry_search block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<?php if ($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<form action="<?php echo $this->action; ?>" name="registry_search" method="post">
<input type="hidden" name="FORM_SUBMIT" value="tl_registry_search" />
<div class="formbody searchWrapper">
	<?php if ($this->message): ?>
		<p class="error"><?php echo $this->message; ?></p>
	<?php endif; ?>
	<div class="col name">
		<label for="lastname"><?php echo $this->lastname; ?></label>
		<input name="lastname" type="text" size="20" value="<?php echo $this->lastnamevalue; ?>" /></div>
	<div class="col date">
		<label for="date"><?php echo $this->datestr; ?></label>
		<input name="date" type="text" size="20" value="<?php echo $this->datevalue; ?>" /></div>
	<div class="submit_container"><a href="javascript:document.registry_search.submit();" onclick="document.registry_search.submit();return false;"><?php echo $this->submitlabel; ?></a></div>
</div>
</form>

</div>
<!-- indexer::start -->