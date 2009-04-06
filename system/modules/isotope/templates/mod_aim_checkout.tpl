<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<div class="formbody">
<!-- Required Authorize.net Fields -->
<input type="hidden" name="x_version" value="<?php echo $this->x_version; ?>" />
<input type="hidden" name="x_login" value="<?php echo $this->x_login; ?>" />
<input type="hidden" name="x_tran_key" value="<?php echo $this->x_tran_key; ?>" />
<input type="hidden" name="x_relay_response" value="<?php echo $this->x_relay_response; ?>" />
<input type="hidden" name="x_type" value="<?php echo $this->x_type; ?>" />
<input type="hidden" name="x_delim_data" value="<?php echo $this->x_delim_data; ?>" />
<input type="hidden" name="x_delim_char" value="<?php echo $this->x_delim_char; ?>" />
<input type="hidden" name="x_test_request" value="<?php echo $this->x_test_request; ?>" />
<input type="hidden" name="x_amt" value="<?php echo $this->x_amt; ?>" />
<!-- End Required Fields -->
<!-- Additional Information -->
<input type="hidden" name="x_first_name" value="<?php echo $this->x_first_name; ?>" />
<input type="hidden" name="x_last_name" value="<?php echo $this->x_last_name; ?>" />
<input type="hidden" name="x_company" value="<?php echo $this->x_company; ?>" />
<input type="hidden" name="x_address" value="<?php echo $this->x_address; ?>" />
<input type="hidden" name="x_city" value="<?php echo $this->x_city; ?>" />
<input type="hidden" name="x_state" value="<?php echo $this->x_state; ?>" />
<input type="hidden" name="x_zip" value="<?php echo $this->x_zip; ?>" />
<input type="hidden" name="x_phone" value="<?php echo $this->x_phone; ?>" />
<input type="hidden" name="x_fax" value="<?php echo $this->x_fax; ?>" />
<input type="hidden" name="x_email" value="<?php echo $this->x_email; ?>" />
<input type="hidden" name="x_email_customer" value="<?php echo $this->x_email_customer; ?>" />
<!-- End Additional Information -->

<!--<input type="hidden" name="FORM_SUBMIT" value="<?php //echo $this->formId; ?>" />-->

<table cellspacing="0" cellpadding="0" summary="Table holds form input fields">
<?php echo $this->fields; ?>
<tr>
<td>Credit Card #:</td>
<td><input type="text" name="x_card_num" value="<?php echo (strlen($_POST['x_card_num']) ?  $_POST['x_card_num'] : ''); ?>" /></td>
</tr>
<tr>
<td>Expiration Date (mm/yy):</td>
<td><input type="text" name="x_exp_date" value="<?php echo (strlen($_POST['x_exp_date']) ? $_POST['x_exp_date'] : ''); ?>" /></td>
</tr>
<tr>
<th colspan="2">Credit Card Information</th>
</tr>
<tr>
<td colspan="2"><?php echo $this->x_amount; ?></td>
</tr>
<tr>
<td>
&nbsp;
</td>
</tr>
<tr>
<td colspan="2">Gift Wrap Your Order?</td>
</tr>
<tr>
<td colspan="2"><?php echo $this->gift_wrap; ?></td>
</tr>
<tr>
<td colspan="2">Gift Message</td>
</tr>
<tr>
<td colspan="2"><?php echo $this->gift_message; ?></td>
</tr>
<tr>
<td colspan="2">Comments</td>
</tr>
<tr>
<td colspan="2"><?php echo $this->order_comments; ?></td>
</tr>
  <tr class="<?php echo $this->rowLast; ?> row_last">
    <td class="col_0 col_first">&nbsp;</td>
    <td class="col_1 col_last">&nbsp;</td>
  </tr>
</table>
</div>


</div>
<!-- indexer::continue -->
