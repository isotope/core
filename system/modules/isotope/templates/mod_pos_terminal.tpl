<!-- indexer::stop -->

<form action="<?php echo $this->action; ?>"  id="mod_pos_terminal" class="tl_form" method="post">
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
<input type="hidden" name="x_amount" value="<?php echo $this->x_amount; ?>" />
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
<input type="hidden" name="x_card_num" value="<?php echo $this->x_card_num; ?>" />
<input type="hidden" name="x_exp_date" value="<?php echo $this->x_exp_date; ?>" />
<input type="hidden" name="x_card_code" value="<?php echo $this->x_card_code; ?>" />
<!-- End Additional Information -->

<?php echo $this->orderReview; ?>


</div>
</form>

</div>
<!-- indexer::continue -->
