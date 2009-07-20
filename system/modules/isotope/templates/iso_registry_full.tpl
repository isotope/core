<div class="iso_cart_full">

<h1 class="title">Title: <?php echo $this->registryTitle; ?></h1>

<p class="name">Name: <?php echo $this->registryOwnerName; ?></p>

<p class="date">Date: <?php echo $this->registryDate; ?></p>

<p class="desc">Details: <?php echo $this->registryDescription; ?></p>

<div class="productWrapper">
<?php if(!sizeof($this->products)): ?>
	<div class="noItems"><?php echo $this->noItemsInCart; ?></div>
<?php else: ?>

<?php foreach($this->products as $product): ?>
		<!-- BEGIN PRODUCT-->
        <div class="product">
   			<div class="col productImg"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['image'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="thumbnail" /></a></div>
       		<div class="col productInfo">
       				<h3 class="productName"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['name']; ?></a></h3>
       				<div class="optionswrapper">
       					
       				</div>
       		</div>
       		<div class="col productQty">
       			<span class="price">Price: <?php echo $product['price']; ?></span> each <br />
       			<span class="qRequested">Number Requested: <?php echo $product['quantity']; ?> </span><br />
       			<span class="qRequested">Number Remaining: <?php echo $product['quantity_remaining']; ?> </span>
       		</div>
        	<div class="col productTotals">
        		                <?php echo $product['add_link']; ?>
            </div>       
            <div class="clearBoth"></div>
		</div>
        <!-- END PRODUCT-->
    <div class="horizontalLine"></div>
    <div class="clearBoth"></div>
	<?php endforeach; ?>
	
<?php endif; ?>

	</div>
	
	
</div>