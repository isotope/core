<div id="invoice">
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
				<?php if(strlen($this->orderTrackingInfoString)): ?>
					<div class="clearBoth"></div>
					<?php echo $this->orderTrackingInfoString; ?>					
				<?php endif; ?>
			</div>
		</div>
		<div id="product_list">
			<table border="0">
				<th><?php echo $this->productNameHeader; ?></th>
				<th><?php echo $this->productSkuHeader; ?></th>
				<th><?php echo $this->productPriceHeader; ?></th>
				<th><?php echo $this->productQuantityHeader; ?></th>
				<th><?php echo $this->productTaxHeader; ?></th>
				<th><?php echo $this->productSubtotalHeader; ?></th>
				<?php echo foreach($this->products as $product): ?>
					<tr>
						<td>
							<h2><?php echo $product['name']; ?></h2>
							<?php if(sizeof($product['options'])): ?>
								<h3><?php echo $this->optionsHeader; ?></h3>	
								<?php foreach($product['options'] as $option): ?>
									<ul>
										<li>
											<h4><?php echo $option['name']; ?></h4>
											<ul>
												<li><?php echo $optoin['value']; ?></li>
											</ul>
										</li>
									</ul>
								<?php endforeach; ?>
							<?php endif; ?>
						</td>
						<td><?php echo $product['sku']; ?></td>
						<td><?php echo $product['price']; ?></td>
						<td><?php echo $product['quantity']; ?></td>
						<td><?php echo $product['tax']; ?></td>
						<td><?php echo $product['subtotal']; ?></td>
					</tr>
				<?php echo endforeach; ?>
			</table>
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
</div>