<h2><?php echo $this->headline; ?></h2>
<p class="message"><?php echo $this->message; ?></p>
<?php if( $this->error ): ?>
<p class="error message"><?php echo $this->error; ?></p>
<?php endif; ?>
<form id="payment_form" action="https://ssl.ditonlinebetalingssystem.dk/auth/default.aspx" method="post">

<table cellspacing="0" cellpadding="0" summary="ePay Payment Form">
	<tr class="cardno">
		<td><label for="ctrl_cardno"><?php echo $this->labelCard; ?><label> <span class="mandatory">*</span></td>
		<td><input type="text" class="text" id="ctrl_cardno" name="cardno" maxlength="19" autocomplete="off" /></td>
	</tr>
	<tr class="expdate">
		<td><label for="ctrl_expmonth"><?php echo $this->labelDate; ?></label> <span class="mandatory">*</span></td>
		<td>
			<select id="ctrl_expmonth" name="expmonth" class="select"><?php echo $this->months; ?></select>&nbsp;
			<select id="ctrl_expyear" name="expyear" class="select"><?php echo $this->years; ?></select>
		</td>
	</tr>
	<tr class="cvc">
		<td><label for="ctrl_cvc"><?php echo $this->labelCCV; ?></label></td>
		<td><input type="text" class="text" name="cvc" id="ctrl_cvc" maxlength="4" autocomplete="off" /></td>
	</tr>
</table>


<input type="hidden" name="merchantnumber" value="<?php echo $this->merchantnumber; ?>">
<input type="hidden" name="orderid" value="<?php echo $this->orderid; ?>">
<input type="hidden" name="description" value="<?php echo $this->description; ?>">
<input type="hidden" name="currency" value="<?php echo $this->currency; ?>">
<input type="hidden" name="amount" value="<?php echo $this->amount; ?>">

<input type="hidden" name="accepturl" value="<?php echo $this->accepturl; ?>">
<input type="hidden" name="declineurl" value="<?php echo $this->declineurl; ?>">

<input type="hidden" name="language" value="2">
<input type="hidden" name="instantcapture" value="<?php echo $this->instantcapture; ?>">
<input type="hidden" name="md5key" value="<?php echo $this->md5key; ?>">
<input type="hidden" name="cardtype" value="0">
<input type="hidden" name="use3D" value="1">

<div class="submit_container">
<input type="submit" class="submit button" value="<?php echo $this->slabel; ?>" />
<a class="button" href="<?php echo $this->cancelurl; ?>">Cancel</a>
</div>

</form>