<div class="iso_registry_manage iso_cart_full">

<?php if ($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<form action="<?php echo $this->registryJumpTo; ?>" method="post" name="registry_manage">
<input type="hidden" name="action" value="update_registry"  />

<h2>Registry Title: <input name="registry_title" size="30" type="text" value="<?php echo $this->registryTitle; ?>" /></h2>
<p class="regdate">Registry Event Date:<br />
	<input name="registry_date" size="30" type="text" value="<?php echo date('m/d/Y', $this->registryDate); ?>" />
</p>
<p class="descr"> Registry Description:<br />
	<textarea name="registry_desc" rows="4" columns="20"><?php echo $this->registryDescription; ?></textarea>
</p>

<div class="productWrapper">
<?php if(!sizeof($this->products)): ?>
	<div class="noItems"><?php echo $this->noItemsInRegistry; ?></div>
<?php else: ?>

<?php foreach($this->products as $product): ?>
		<!-- BEGIN PRODUCT-->
        <div class="product">
        
        <!--IF USER IS LOGGED IN-->
        <?php if(USERISLOGGEDIN): ?>
        	<div class="col removeButton"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>">x</a> Remove</div>
        <?php endif; ?>
       	<!--END IF USER IS LOGGED IN-->
        	
   			<div class="col productImg"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['image'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="thumbnail" /></a></div>
       		
       		<div class="col productInfo">
       				<h3 class="productName"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['name']; ?></a></h3>
       				<!--<div class="optionswrapper">
       					<?php //foreach($this->cart_options as $option): ?>
							<div class="option"><span class="optionname">OPTION:</span> PRODUCT OPTION</div>
						<?php //endforeach; ?>
       				</div>-->
       		</div>
       		
       		<div class="col productQtyRequested">
       			<p class="price"><?php echo $product['price']; ?></p>
       			
       			
       			<p class="qtyRequested">Quantity Requested: 
       			
       				<input name="product_qty_<?php echo $product['id']; ?>" type="text" size="3" value="<?php echo $product['quantity']; ?>" />
       				       			
       		</div>
       		    
            <div class="clear">&nbsp;</div>
		</div>
        <!-- END PRODUCT-->
        <div class="divider"></div>   
	<?php endforeach; ?>
<?php endif; ?>

	</div>
	<div class="registryButtons">
		<div class="submit_container"><a href="javascript:document.registry_manage.submit();" onclick="document.registry_manage.submit();return false;"><?php echo $this->submitlabel; ?></a></div>
	</div>
	
	</form>
	
</div>