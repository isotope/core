<div id="invoice" style="margin:18px; padding:0px; font-size: 62.5%; font-family: Arial, Helvetica, sans-serif; width:800px; border:solid 1px #000000; padding-left25px; padding-right:25px;">
	<table id="header" cellpadding="5" cellspacing="0" border="0" width="100%">
		<?php if($this->logoImage): ?>
		<tr>
		<td id="logo"><img src="<?php echo $this->logoImage; ?>" /></td>
		<td style="text-align:right;"><p style="font-size:14px; margin-top:0px; margin-bottom:10px; padding:0px;"><?php echo $this->invoiceTitle; ?></p></td>
		</tr>
		<?php endif; ?>
		<tr>
		<td id="basic_info">
			<p><?php echo $this->customerNameString; ?> - <?php echo $this->customerEmailString; ?></p>
			<?php echo $this->customerPhoneString; ?>
		</td>
	</tr>
	</table>
	
	<table id="addressPayment" cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr>
		<td id="billing_address">
				<h2><?php echo $this->orderBillingAddressHeader; ?></h2>
				<?php echo $this->orderBillingAddressString; ?>
		</td> 
		<td id="shipping_address">
				<h2><?php echo $this->orderShippingAddressHeader; ?></h2>
				<?php echo $this->orderShippingAddressString; ?> 
		</td>

			<td id="payment_shipping">
				<h2><?php echo $this->paymentInfoHeader; ?></h2>
				<?php echo $this->paymentInfoString; ?>
				<h2><?php echo $this->shippingInfoHeader; ?></h2>
				<?php echo $this->shippingInfoString; ?>
				<?php if(strlen($this->orderTrackingInfoString) > 0): ?>
					<?php echo $this->orderTrackingInfoString; ?>					
				<?php endif; ?>
			</td>
		</tr>
	</table>
		
	<table id="products" cellpadding="5" cellspacing="0" border="1" width="1500">
		<tr>
			<td align="left" bgcolor="#eaeaea" width="50"><strong><?php echo $this->productSkuHeader; ?></strong></td>
			<td align="left" bgcolor="#eaeaea" width="250"><strong><?php echo $this->productNameHeader; ?></strong></td>
			<td align="left" bgcolor="#eaeaea" width="50"><strong><?php echo $this->productPriceHeader; ?></strong></td>
			<td align="left" bgcolor="#eaeaea" width="50"><strong><?php echo $this->productQuantityHeader; ?></strong></td>
			<td align="left" bgcolor="#eaeaea" width="50"><strong><?php echo $this->productTaxHeader; ?></strong></td>
			<td align="left" bgcolor="#eaeaea" width="50"><strong><?php echo $this->productSubtotalHeader; ?></strong></td>
		</tr>	
		<?php foreach($this->products as $product): ?>
		<tr>
			<td align="left" width="50"><?php echo $product['sku']; ?></td>
			<td align="left" width="250">
				<?php echo $product['name']; ?>
				<?php if(sizeof($product['options'])): ?>
					<p><strong><?php echo $this->optionsHeader; ?></strong></p>
					<?php echo $product['options']; ?>
				<?php endif; ?>		
			</td>
			<td align="left" width="50"><?php echo $product['price']; ?></td>
			<td align="left" width="50"><?php echo $product['quantity']; ?></td>
			<td align="left" width="50"><?php echo $product['tax']; ?></td>
			<td align="left" width="50"><?php echo $product['subtotal']; ?></td>
			</tr>			
		<?php endforeach; ?>
	</table>
	
	<table id="divider" cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#000000"><tr><td></td></tr></table><p></p>	
	
		<table id="summary" cellpadding="5" cellspacing="0" border="0" width="1500">			
			<tr>
				<td width="275">&nbsp;</td>
				<td width="150" align="left" style="white-space:nowrap;"><?php echo $this->orderSubtotalHeader; ?></td>
				<td width="50" align="left" style="white-space:nowrap;"><?php echo $this->orderSubtotal; ?></td>
			</tr>
			<tr>
				<td width="275">&nbsp;</td>
				<td width="150" align="left" style="white-space:nowrap;"><?php echo $this->orderTaxHeader; ?></td>
				<td width="50" align="left" style="white-space:nowrap;"><?php echo $this->orderTaxTotal; ?></td>
			</tr>
			<tr>
				<td width="275">&nbsp;</td>
				<td width="150" align="left" style="white-space:nowrap;"><?php echo $this->orderShippingHeader; ?></td>
				<td width="50" align="left" style="white-space:nowrap;"><?php echo $this->orderShippingTotal; ?></td>
			</tr>
			<tr>
				<td width="275">&nbsp;</td>
				<td width="150" align="left" style="white-space:nowrap;"><h2><?php echo $this->orderGrandTotalHeader; ?></h2></td>
				<td width="75" align="left" style="white-space:nowrap;"><h2><?php echo $this->orderGrandTotal; ?></h2></td>
			</tr>

		</table>
	
	<table id="footer" cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr><td>
			<?php echo $this->orderFooterString; ?>
		</td></tr>
	</table>
	
</div>