<form action="<?php echo $this->action; ?>" method="get">
<div class="formbody">
<?php if ($this->id): ?>
<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
<?php endif; ?>
<table cellpadding="0" cellspacing="0">
<tr>
<td class="name"><p><label for="lastname"><?php echo $this->lastnameLabel; ?></label></p></td>
<td><p><input id="ctrl_lastname" name="lastname" type="text" size="20" value="<?php echo $_GET['lastname']; ?>" /></p></td>
<td class="date"><p><label for="date"><?php echo $this->dateLabel; ?></label></td>
<td><p><input id="ctrl_date" name="date" type="text" size="20" value="<?php echo $_GET['date']; ?>" /></p></td>
<td colspan="2">
<div class="submit_container"><input type="submit" id="ctrl_submit" class="submit" value="<?php echo $this->searchRegistry; ?>" /></div>
</td>
</tr>
</table>
</div>
</form>