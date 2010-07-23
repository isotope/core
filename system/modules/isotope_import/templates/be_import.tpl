<div id="tl_buttons">
<a href="<?php echo $this->href; ?>" class="header_back" title="<?php echo $this->title; ?>"><?php echo $this->button; ?></a>
</div>
<h2 class="sub_headline"><?php echo $this->importHeadline; ?></h2>
<div id="tl_import" style="padding-bottom:25px;">
<?php if ($this->importMessage): ?>
<div class="tl_message">
<?php echo $this->importMessage; ?>
</div>
<?php endif; ?>
<?php if (!$this->readyForImport): ?>

<?php if ($this->setOldDb): ?>
<form action="<?php echo $this->action; ?>" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_import_setdb" />
<fieldset class="tl_tbox block">
<div>
<h3><label for="import_db">Old Database Name</label></h3>
<input type="text" name="import_db" id="ctrl_import_db" class="tl_text" />
<p class="tl_help tl_tip">Please enter the full name of the old database loaded on the same host. Note that the same user must have full privileges to that one as well.</p>
</div>
<div class="tl_submit_container">
<input type="submit" name="clear" id="clear" class="tl_submit" value="Next" /> 
</div>
<?php endif; ?>
<?php if ($this->setCats): ?>
<form action="<?php echo $this->action; ?>" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_import_setcats" />
<fieldset class="tl_tbox block">
<h3>Old Database Name: <?php echo $this->oldDb; ?></h3>
<div>
<h3><label for="ctrl_options">Category Mapping</label></h3>
<table cellspacing="0" cellpadding="0" class="tl_optionwizard" id="ctrl_options" summary="Field wizard">
  <thead>
    <tr>
      <th>Old Category</th>
      <th style="padding-left:36px;">New Category</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><select id="options_value_0" name="options[0][value]"><option value="">-</option><?php echo $this->oldoptions; ?></select></td>
      <td class="arrow"><select id="options_label_0" name="options[0][label]"><option value="">-</option><?php echo $this->newoptions; ?></select></td>
      <td style="white-space:nowrap; padding-left:3px;"><a href="#" title="Duplicate field" onclick="BackendImport.categoryWizard(this, 'copy', 'ctrl_options'); return false;"><img src="system/themes/default/images/copy.gif" width="14" height="16" alt="Duplicate field"></a> <a href="#" title="Delete field" onclick="BackendImport.categoryWizard(this, 'delete', 'ctrl_options'); return false;"><img src="system/themes/default/images/delete.gif" width="14" height="16" alt="Delete field"></a> </td>
    </tr>
  </tbody>
  </table>
</div>
</fieldset>
<div class="tl_submit_container">
<input type="submit" name="clear" id="clear" class="tl_submit" value="Set Values" /> 
</div>
<?php endif; ?>
<?php if ($this->setConfirm): ?>
<form action="<?php echo $this->action; ?>" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_import_confirm" />
<fieldset class="tl_tbox block">
<h2>Please confirm the following information:</h2>
<h3>Old Database Name: <?php echo $this->oldDb; ?></h3>
<div>
<h3><label for="ctrl_options">Category Mapping</label></h3>
<table cellspacing="0" cellpadding="0" class="tl_optionwizard" id="ctrl_options" summary="Field wizard">
  <thead>
    <tr>
      <th>Old Category</th>
      <th style="padding-left:36px;">New Category</th>
    </tr>
  </thead>
  <tbody>
      <?php echo $this->categories; ?>
  </tbody>
  </table>
</div>
</fieldset>
<div class="tl_submit_container">
<input type="submit" name="clear" id="clear" class="tl_submit" value="Confirm settings" /> 
</div>
<?php endif; ?>
</form>
</div>
<?php else: ?>
<form action="<?php echo $this->action; ?>" class="tl_form" method="post" onsubmit="ImportRequest.startImport(this, 'start'); return false;" >
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_import" />
<div class="tl_submit_container">
<input type="submit" name="clear" id="clear" class="tl_submit" value="<?php echo $this->importSubmit; ?>" /> 
</div>
</div>
</form>
<?php endif; ?>
</div>