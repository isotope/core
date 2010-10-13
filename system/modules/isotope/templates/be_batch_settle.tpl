<?php if($this->orders): ?>
<table id="batch_settle_listing" border="0" cellpadding="5" cellspacing="0" summary="Settle transactions for authorize.net">
<thead>
	<th><input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)" /><label for="check_all" style="color:#a6a6a6;"><em><?php echo $this->checkAllLabel; ?></em></label></th>
    <th><?php echo $this->ordersLabel; ?></th>
	<th><?php echo $this->statusLabel; ?></th>
</thead>
<tbody>
<?php foreach($this->orders as $order): ?>
<tr>
	<td><input type="checkbox" class="tl_checkbox" id="ctrl_order_id_<?php echo $order['id']; ?>" name="order_id[]" value="<?php echo $order['id']; ?>" /></td>
    <td><?php echo $order['label']; ?></td>
    <td><div id="batchStatus"><?php echo $order['status']; ?></div></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif;?>
