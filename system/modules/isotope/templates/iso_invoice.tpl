<div id="invoice" style="margin:18px; padding:0px; font-size: 62.5%; font-family: Arial, Helvetica, sans-serif; width:800px; border:solid 1px #000000; padding-left25px; padding-right:25px;">
	<!--<table id="header" cellpadding="5" cellspacing="0" border="0" width="100%">
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
	</table>-->
	<?php if($this->logoImage): ?>
	<table id="header" cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr>
		<td id="logo"><img src="<?php echo $this->logoImage; ?>" /></td>
		<td style="text-align:right;"><p style="font-size:14px; margin-top:0px; margin-bottom:10px; padding:0px;"><?php echo $this->invoiceTitle; ?></p></td>
		</tr>
	</table>
	<?php endif; ?>
		
	<h2><?php echo $this->orderDetailsHeadline; ?></h2>
	
	<?php foreach( $this->info as $type => $data ): ?>
	<div class="info_container <?php echo $type . $data['class']; ?>">
		<h3><?php echo $data['headline']; ?></h3>
		<div class="info"><?php echo $data['info']; ?></div>
	</div>
	<?php endforeach; ?>
	<div class="clear">&nbsp;</div>
	
	<table cellspacing="0" cellpadding="0" summary="Order items">
		<tfoot>
			<tr class="subtotal foot_first">
				<td class="col_first name" colspan="2"><?php echo $this->subTotalLabel; ?></td>
				<td class="price">&nbsp;</td>
				<td class="price total"><?php echo $this->subTotalPrice; ?></td>
				<td class="col_last tax">&nbsp;</td>
			</tr>
	<?php if (is_array($this->surcharges)): foreach( $this->surcharges as $surcharge ): ?>
			<tr>
				<td class="col_first name" colspan="2"><?php echo $surcharge['label']; ?></td>
				<td class="price"><?php echo $surcharge['price']; ?></td>
				<td class="price total"><?php echo $surcharge['total_price']; ?></td>
				<td class="col_last tax"><?php echo $surcharge['tax_id']; ?></td>
			</tr>
	<?php endforeach; endif; ?>
			<tr class="grandtotal foot_last">
				<td class="col_first name" colspan="2"><?php echo $this->grandTotalLabel; ?></td>
				<td class="price total" colspan="2"><?php echo $this->grandTotal; ?></td>
				<td class="col_last tax">&nbsp;</td>
			</tr>
		</tfoot>
		<tbody>
	<?php foreach( $this->items as $item ): ?>
			<tr>
				<td class="col_first col_0 name"><?php if (strlen($item['href'])): ?><a href="<?php echo $item['href']; ?>"><?php endif; echo $item['name']; if (strlen($item['href'])): ?></a><?php endif; ?>
					<?php if(is_array($item['product_options']) && count($item['product_options'])): ?>
					<div class="optionswrapper">
						<ul class="productOptions">
						<?php foreach($item['product_options'] as $option): ?>
							<li><strong><?php echo $option['name']; ?>:</strong> <?php echo implode(', ', $option['values']); ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</td>
				<td class="col_1 quantity"><?php echo $item['quantity']; ?></td>
				<td class="col_2 price"><?php echo $item['price']; ?></td>
				<td class="col_3 price total"><?php echo $item['total']; ?></td>
		    	<td class="col_4 col_last tax"><?php echo $product['tax_id']; ?></td>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>
	
	
	<?php if (count($this->downloads)): ?>
	<h2><?php echo $this->downloadsLabel; ?></h2>
	<?php foreach( $this->downloads as $download ): ?>
	<div class="download"><?php if ($download['downloadable']): ?><a href="<?php echo $download['href']; ?>" /><?php endif; echo $download['title']; if ($download['downloadable']): ?></a><?php endif; echo $download['remaining']; ?></div>
	<?php endforeach; endif; ?>	
</div>