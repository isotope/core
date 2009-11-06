<div class="iso_checkout_order_review">
	<div class="productWrapper">
	
		<table class="productTable" cellspacing="0" cellpadding="0" summary="Cart products">
			<tbody>
		<?php foreach($this->products as $product): ?>
				<!-- BEGIN PRODUCT-->
		        <tr class="product">
		   			<td class="col productImg"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['image'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="thumbnail" /></a></td>
		       		<td class="col productInfo">
		       				<h3 class="productName"><?php echo $product['name']; ?></h3>
		       				<div class="optionswrapper">
		       					<?php if(sizeof($product['option_values'])>0): ?>
			       					<ul class="productOptions">
			       					<?php foreach($product['option_values'] as $option): ?>
			       						<li><strong><?php echo $option['name']; ?>:</strong> <?php echo join(', ', $option['values']); ?></li>
									<?php endforeach; ?>
									</ul>
						<?php endif; ?>
		       				</div>
		       		</td>
		       		<td class="col productQty">
		       			<span class="price"><?php echo $product['price']; ?></span>
		       		</td>
					<td class="col qtyModifier"> x <?php echo $product['quantity']; ?></td>
		        	<td class="col productTotals">                 
		                   <div class="total"><span class="total"><?php echo $product['total_price']; ?></span></div>
		            </td>
				</tr>
		        <!-- END PRODUCT-->
			<?php endforeach; ?>
			</tbody>
		</table>
	    <div class="finalPrices">
	    	<div class="subTotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>
			<div class="shipping"><span class="label"><?php echo $this->shippingLabel; ?></span> <?php echo $this->shippingTotal; ?></div>
            <!--<?php if(strlen($this->shippingOptionsList)): ?>
            <div class="shipping"><span class="label"><?php echo $this->shippingOptionsListLabel; ?></span><br /><?php echo $this->shippingOptionsList; ?></div>
	    	<?php endif; ?>-->
            <div class="tax"><span class="label"><?php echo $this->taxLabel; ?></span> <?php echo $this->taxTotal; ?></div>
	    	<div class="grandTotal"><span class="label"><?php echo $this->grandTotalLabel; ?></span> <?php echo $this->grandTotalPrice; ?></div>
	    	<div class="clearBoth"></div>
	    </div>
	
	</div>
	<div class="clearBoth"></div>
	<div class="billing_address">
		<h2>Billing<?php echo strlen($this->shippingAddress) ? '' : ' & Shipping'; ?> Address</h2>
		<?php echo nl2br($this->billingAddress); ?>
	</div>
<?php if (strlen($this->shippingAddress)): ?>
	<div class="shipping_address">
		<h2>Shipping Address</h2>
		<?php echo nl2br($this->shippingAddress); ?>
	</div>
<?php endif; ?>
	<div class="clearBoth"></div>
	<div class="shipping_method"><strong>Shipping Method:</strong> <?php echo $this->shippingMethod; ?></div>
	<div class="payment_method"><strong>Payment Method:</strong> <?php echo $this->paymentMethod; ?></div>
	<div class="clearBoth"></div>
	<?php if($this->transactionFailure): ?>
	<div class="payment_response error">
	<h2>We're sorry, there seems to be a problem...</h2><?php echo 'Reason: ' . $this->reason; ?>
	</div>
	<?php endif; ?>
	<div class="payment_form"><h2>Enter Your Payment Information:</h2><?php echo $this->checkoutForm; ?></div>		
</div>

