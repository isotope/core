<div class="iso_checkout_order_review">

<div class="productWrapper">

<?php foreach($this->products as $product): ?>
		<!-- BEGIN PRODUCT-->
        <div class="product">
   			<div class="col productImg"><img src="<?php echo $product['image'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="thumbnail" /></div>
       		<div class="col productInfo">
       				<h3 class="productName"><?php echo $product['name']; ?></h3>
       				<!--<div class="optionswrapper">
       					<?php foreach($this->cart_options as $option): ?>
							<div class="option"><span class="optionname">OPTION:</span> PRODUCT OPTION</div>
						<?php endforeach; ?>
       				</div>-->
       		</div>
       		<div class="col productQty">
       			<span class="price"><?php echo $product['price']; ?></span> x <?php echo $product['quantity']; ?>
       		</div>
        	<div class="col productTotals">                 
                   <div class="total"><span class="total"><?php echo $product['total_price']; ?></span></div>
            </div>       
            <div class="clearBoth"></div>
		</div>
        <!-- END PRODUCT-->
        <div class="divider"></div>   
	<?php endforeach; ?>
    <div class="horizontalLine"></div>
    <div class="clearBoth"></div>
    <div class="finalPrices">
    	<div class="subTotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div>
		<div class="shipping"><span class="label"><?php echo $this->shippingLabel; ?></span> <?php echo $this->shippingTotal; ?></div>
    	<div class="tax"><span class="label"><?php echo $this->taxLabel; ?></span> <?php echo $this->taxTotal; ?></div>
    	<div class="grandTotal"><span class="label"><?php echo $this->grandTotalLabel; ?></span> <?php echo $this->grandTotalPrice; ?></div>
    	<div class="clearBoth"></div>
    </div>

	</div>
	
</div>