<div id="invoice" style="margin:18px; padding:0px;">
	<div id="header">
		<div id="logo"></div><div><h1><?php echo $this->invoiceTitle; ?></h1></div>
		<div class="clearBoth"></div>
		<div id="basic_info">
			<h2><?php echo $this->customerNameString; ?> - <?php echo $this->customerEmailString; ?></h2>
			<?php echo $this->customerPhoneString; ?>
		</div>
	</div>
	
	<div class="clearBoth"></div>
	<div id="body">
		<div id="address_info">
			<div id="billing_address">
				<h2><?php echo $this->orderBillingAddressHeader; ?></h2>
				<?php echo $this->orderBillingAddressString; ?>
			</div> 
			<div id="shipping_address">
				<h2><?php echo $this->orderShippingAddressHeader; ?></h2>
				<?php echo $this->orderShippingAddressString; ?> 
			</div>
		</div>
		<div class="clearBoth"></div>
		<div id="payment_and_shipping_info">
			<div id="payment_method">
				<h2><?php echo $this->paymentInfoHeader; ?></h2>
				<?php echo $this->paymentInfoString; ?>
			</div> 
			<div id="shipping_method">
				<h2><?php echo $this->shippingInfoHeader; ?></h2>
				<?php echo $this->shippingInfoString; ?>
				<?php if(strlen($this->orderTrackingInfoString) > 0): ?>
					<div class="clearBoth"></div>
					<?php echo $this->orderTrackingInfoString; ?>					
				<?php endif; ?>
			</div>
		</div>
		<div id="product_list" style="font-size: 10px;">
			<table border="0" cellpadding="5" cellspacing="0">
				<thead>
					<th align="left"><?php echo $this->productSkuHeader; ?></th>
				</thead>
				<tbody>				
				<?php foreach($this->products as $product): ?>
					<tr>
						<td>
							<h2><?php echo $product['sku']; ?> - <?php echo $this->productQuantityHeader; ?>: <?php echo $product['quantity']; ?> - <?php echo $product['name']; ?></h2>
						</td>
					</tr>
					<tr>
						<td>
					  		<table border="0" cellpadding="0" cellspacing="0">
					  			<thead>
									<tr>
										<th align="left"><?php echo $this->productPriceHeader; ?></th>
										<th align="left"><?php echo $this->productQuantityHeader; ?></th>
										<th align="left"><?php echo $this->productTaxHeader; ?></th>
										<th align="left"><?php echo $this->productSubtotalHeader; ?></th>
					  				</tr>
					  			</thead>
					  			<tbody>
									<tr>
										<td align="left"><?php echo $product['price']; ?></td>
										<td align="left"><?php echo $product['quantity']; ?></td>
										<td align="left"><?php echo $product['tax']; ?></td>
										<td align="left"><?php echo $product['subtotal']; ?></td>
					  				</tr>
					  			</tbody>					  			
					  		</table>
						</td>
					</tr>
					<?php if(sizeof($product['options'])): ?>
					<tr>
						<td align="left">
							<strong><?php echo $this->optionsHeader; ?></strong>	
							<?php foreach($product['options'] as $option): ?>
								<ul>
									<li>
										<h4><?php echo $option['name']; ?></h4>
										<ul>
											<li><?php echo $option['value']; ?></li>
										</ul>
									</li>
								</ul>
							<?php endforeach; ?>
						</td>
					</tr>	
					<?php endif; ?>				
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="clearBoth"></div>
			<div id="cost_summary">
				<table border="0" cellpadding="15" cellspacing="0">
					<tbody>				
						<tr>
							<td align="left"><?php echo $this->orderSubtotalHeader; ?></td>
							<td align="left"><?php echo $this->orderSubtotal; ?></td>
						</tr>
						<tr>
							<td align="left"><?php echo $this->orderTaxHeader; ?></td>
							<td align="left"><?php echo $this->orderTaxTotal; ?></td>
						</tr>
						<tr>
							<td align="left"><?php echo $this->orderShippingHeader; ?></td>
							<td align="left"><?php echo $this->orderShippingTotal; ?></td>
						</tr>
						<tr>
							<td align="left"><?php echo $this->orderGrandTotalHeader; ?></td>
							<td align="left"><?php echo $this->orderGrandTotal; ?></td>
						</tr>
					</tbody>	
				</table>
			</div>
		</div>
		<div class="clearBoth"></div>
		<div id="footer"><?php echo $this->orderFooterString; ?></div>
	</div>
</div>		
<!--
<div id="invoice" style="margin:18px; padding:0px;">
	<div id="header"><div id="logo"></div><div><h1><?php echo $this->invoiceTitle; ?></h1></div></div>
	<div class="clearBoth"></div>
	<div id="body">
		<div id="address_info">
			<div id="billing_address">
				<h2><?php echo $this->orderBillingAddressHeader; ?></h2>
				<?php echo $this->orderBillingAddressString; ?>
			</div> 
			<div id="shipping_address">
				<h2><?php echo $this->orderShippingAddressHeader; ?></h2>
				<?php echo $this->orderShippingAddressString; ?> 
			</div>
		</div>
		<div class="clearBoth"></div>
		<div id="payment_and_shipping_info">
			<div id="payment_method">
				<h2><?php echo $this->paymentInfoHeader; ?></h2>
				<?php echo $this->paymentInfoString; ?>
			</div> 
			<div id="shipping_method">
				<h2><?php echo $this->shippingInfoHeader; ?></h2>
				<?php echo $this->shippingInfoString; ?>
				<?php if(strlen($this->orderTrackingInfoString) > 0): ?>
					<div class="clearBoth"></div>
					<?php echo $this->orderTrackingInfoString; ?>					
				<?php endif; ?>
			</div>
		</div>
		<div id="product_list">
			<div class="fieldHeaders" style="background-color: #eeeeee; border:1px solid; line-height:24px; height: 20px; width: 100%; padding:3px; font-size: 10pt;"><div style="float: left; width: 150px; padding-left: 10px; font-size: 10pt;"><?php echo $this->productNameHeader; ?></div><div style="float: left; width:100px; padding-left: 10px; font-size: 10pt;"><?php echo $this->productSkuHeader; ?></div><div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $this->productPriceHeader; ?></div><div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $this->productQuantityHeader; ?></div><div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $this->productTaxHeader; ?></div><div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $this->productSubtotalHeader; ?></div></div>
			<div class="clearBoth" style="clear: both;"></div>
			<div id="productRows" style="padding-left: 3px;">
			<?php $i=0; ?>
			<?php foreach($this->products as $product): ?>
				<div id="row" style="padding-top: 20px; padding-bottom:20px;<?php echo (!is_int($i / 2) ? ' background-color: #cccccc;' : ''); ?>">
					<div style="float: left; width:150px; font-size: 10pt;"><?php echo $product['name']; ?>
					<?php if(sizeof($product['options'])): ?>
						<br /><br />
						<strong><?php echo $this->optionsHeader; ?></strong>	
						<?php foreach($product['options'] as $option): ?>
							<ul>
								<li>
									<h4><?php echo $option['name']; ?></h4>
									<ul>
										<li><?php echo $option['value']; ?></li>
									</ul>
								</li>
							</ul>
						<?php endforeach; ?>
					<?php endif; ?>
					</div>
					<div style="float: left; width:100px; padding-left: 10px; font-size: 10pt;"><?php echo $product['sku']; ?></div> 
					<div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $product['price']; ?></div>
					<div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $product['quantity']; ?></div>
					<div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $product['tax']; ?></div>
					<div style="float: left; width: 80px; padding-left: 10px; text-align: center; font-size: 10pt;"><?php echo $product['subtotal']; ?></div>
				</div>
				<div class="clearBoth" style="clear: both;"></div>
				<?php $i++; ?>
			<?php endforeach; ?>
			</div>
			
			
		</div>
		<div class="clearBoth"></div>
		<div id="cost_summary">
			<table border="0" cellpadding="15" cellspacing="0">
				<tr>
					<td><?php echo $this->orderSubtotalHeader; ?></td>
					<td><?php echo $this->orderTaxHeader; ?></td>
					<td><?php echo $this->orderShippingHeader; ?></td>
					<td><?php echo $this->orderGrandTotalHeader; ?></td>
				</tr>
			</table>
		</div>
	</div>
	<div class="clearBoth"></div>
	<div id="footer"><?php echo $this->orderFooterString; ?></div>
</div>-->