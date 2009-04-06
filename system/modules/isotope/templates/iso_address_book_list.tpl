<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>
<div class="address_book_list">
<table cellpadding="8" cellspacing="0" class="all_records" summary="">
<thead>
  <tr>
    <th class="head<?php echo $col['class']; ?>"><?php echo $this->addressLabel; ?></th>
    <th class="head<?php echo $col['class']; ?>"><?php echo $this->editAddressLabel; ?></th>
    <th class="head<?php echo $col['class']; ?>"><?php echo $this->deleteAddressLabel; ?></th>
  </tr>
</thead>
<tbody>
<?php if(sizeof($this->addresses)): ?>
<?php foreach ($this->addresses as $address): ?>
  <tr class="<?php echo $class; ?>">
    <td class="body"><?php echo $address['text']; ?></td>
    <td class="body" align="center"><a href="<?php echo $address['edit_url']; ?>" title="<?php echo $this->editAddressLabel; ?>"><img src="<?php echo $this->isotopeBase; ?>/images/edit.png" border="0" width="16" height="16" alt="<?php echo $this->editAddressLabel; ?>" /></a></td>
    <td class="body" align="center"><a href="<?php echo $address['delete_url']; ?>" title="<?php echo $this->deleteAddressLabel; ?>"><img src="<?php echo $this->isotopeBase; ?>/images/delete.png" border="0" width="16" height="16" alt="<?php echo $this->deleteAddressLabel; ?>" /></a></td>
  </tr>
<?php endforeach; ?>
<?php else: ?>
 <tr>
 	<td colspan="3" class="body" align="center"><?php echo $this->message; ?></td>
 </tr>
<?php endif; ?>
  <tr>
    <td colspan="2" class="body" align="center"><ul style="list-style-type:none;"><li style="height: 16px; padding-left: 20px;
background-image: url(<?php echo $this->isotopeBase; ?>/images/add.png);background-repeat: no-repeat;background-position: 0em -0.1em;"><a href="<?php echo $this->addNewAddress; ?>" title="<?php echo $this->addNewAddressLabel; ?>"><?php echo $this->addNewAddressLabel; ?></a></li></ul></td>
  </tr>
</tbody>
</table>
</div>
</div>