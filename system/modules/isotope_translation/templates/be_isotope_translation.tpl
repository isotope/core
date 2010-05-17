<form action="<?php echo $this->action; ?>" class="tl_form" method="post" name="tl_select_translation">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_translation_filters">

<div class="tl_panel">

<div class="tl_submit_panel tl_subpanel">
<input type="image" name="btfilter" id="btfilter" src="system/themes/default/images/ok.gif" class="tl_img_submit" alt="apply changes" value="apply changes" />
</div>

<div class="tl_filter tl_subpanel">
<select name="module" id="module" class="tl_select<?php echo $this->moduleClass; ?>" onChange="document.tl_select_translation.submit();">
  <option value="">Modul</option>
<?php foreach( $this->modules as $module ): ?>
	<option value="<?php echo $module['value']; ?>"<?php echo $module['default'] ? ' selected="selected"' : ''; ?>><?php echo $module['label']; ?></option>
<?php endforeach; ?>
</select>

<select name="file" id="file" class="tl_select<?php echo $this->fileClass; ?>" onChange="document.tl_select_translation.submit();">
	<option value="">-File-</option>
<?php foreach( $this->files as $file ): ?>
	<option value="<?php echo $file['value']; ?>"<?php echo $file['default'] ? ' selected="selected"' : ''; ?>><?php echo $file['label']; ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="clear"></div>

</div>
</div>
</form>


<div class="tl_listing_container">
<?php if(strlen($this->edit)): ?>

<form action="<?php echo $this->action; ?>" method="post">
	<h2 class="sub_headline"><?php echo $this->headline; ?></h2>
<?php echo $this->getMessages() . '<br />'; ?>
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="isotope_translation" />

<table cellpadding="0" cellspacing="0" class="tl_listing" summary="Table lists records">

	<?php foreach ( $this->source as $key => $value): ?>
  	  <tr onmouseover="Theme.hoverRow(this, 1);" onmouseout="Theme.hoverRow(this, 0);">
	    <td class="tl_file_list" style="width: 50%"><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></td>
	    <td class="tl_file_list" style="width: 50%">= <input type="text" name="<?php echo standardize($key); ?>" class="tl_text" value="<?php echo str_replace('"', '&quot;', $this->translation[$key]); ?>" onfocus="Backend.getScrollOffset();" /></td>
	  </tr>
	<?php endforeach; ?> 
	
</table>

</div>
</div>

<div class="tl_formbody_submit">
<div class="tl_submit_container"><input type="submit" name="save" id="save" class="tl_submit" alt="save all changes" accesskey="s" value="<?php echo $this->slabel; ?>" /></div>
</form>

<?php else: ?><?php if ($this->error): ?>
<p class="tl_error"><?php echo $this->error; ?></p><?php endif; ?>
<p class="tl_info"><?php echo $this->headline; ?></p>
<?php endif; ?>
</div>
