<script type="text/javascript">
	hs.graphicsDir = 'plugins/highslide/graphics/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.outlineType = 'rounded-white';
	hs.fadeInOut = true;
	hs.numberPosition = 'caption';
	hs.dimmingOpacity = 0.75;
	hs.showCredits = false;

	// Add the controlbar
	if (hs.addSlideshow) hs.addSlideshow({
		slideshowGroup: 'productImages',
		interval: 5000,
		repeat: false,
		useControls: true,
		fixedControls: 'fit',
		overlayOptions: {
			opacity: .75,
			position: 'bottom center',
			hideOnMouseOut: true
		}
	});
</script>
<div class="mod_productReader">
<?php if (strlen($this->productName)): ?><h1><?php echo $this->productName; ?></h1><?php endif; ?>
  <div class="productInfoWrap">
    <?php if($this->hasErrors): ?>
    <div class="errorMessages">
		<?php foreach($this->errorMessages as $error): ?>
    		<div class="error"><?php echo $error; ?></div>
        <?php endforeach; ?>
    </div>    
    <?php else: ?>
    <?php foreach($this->productCollection as $product): ?>
    <div class="column1">
      <div class="mainImage">
      	<?php if($this->mainImage['has_large_image']): ?>
            <noscript>
            	<a href="<?php echo $this->mainImage['large_image_link']; ?>" title="<?php echo $product['name']; ?>" target="_blank"><img src="<?php echo $this->mainImage['file_path']; ?>" width="<?php echo $this->mainImage['width']; ?>" height="<?php echo $this->mainImage['height']; ?>" alt="<?php echo $this->mainImage['alt']; ?>" class="productImg" /></a>
            </noscript>
            <a href="<?php echo $this->mainImage['large_image_link']; ?>" <?php echo $this->mainImage['on_thumbnail_click_event']; ?> title="<?php echo $product['name']; ?>"><img src="<?php echo $this->mainImage['file_path']; ?>" width="<?php echo $this->mainImage['width']; ?>" height="<?php echo $this->mainImage['height']; ?>" alt="<?php echo $this->mainImage['alt']; ?>" class="productImg" /></a>
        <?php else: ?>
            <img src="<?php echo $this->mainImage['file_path']; ?>" width="<?php echo $this->mainImage['width']; ?>" height="<?php echo $this->mainImage['height']; ?>"  alt="<?php echo $this->mainImage['alt']; ?>" class="productImg" />
        <?php endif; ?></div>
      <!-- <p class="caption"><?php //echo $product['name']; ?></p>-->
    </div>
    <div class="column2">
      <form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="<?php echo $this->method; ?>" enctype="<?php echo $this->enctype; ?>"<?php echo $this->attributes; ?>>
		<div class="formbody">
		<?php if ($this->method != 'get'): ?>
		<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formId; ?>" />
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->maxFileSize; ?>" />
		<input type="hidden" name="action" value="add_to_cart" />
		<input type="hidden" name="id" value="<?php echo $product['id']; ?>" />
		<input type="hidden" name="aset_id" value="<?php echo $product['aset_id']; ?>" />
		<input type="hidden" name="option_fields" value="<?php echo $this->optionFields; ?>" />
		<?php endif; ?>
		<?php echo $this->hidden; ?>
		<h2 class="productName"><?php echo $this->productDetailLabel; ?></h2>
	      <div class="pricing">
	        <p class="productPrice"><?php echo $product['price_string']; ?></p>
	        <p class="sku"><?php echo $product['sku']; ?></p>
	        <div class="clearBoth"></div>
	      </div>
	      <?php if(strlen($this->embeddedMedia) > 0): ?>
	      <div class="media"><?php echo $this->embeddedMedia; ?></div>
	      <?php endif; ?>	      
	      <?php if($this->hasOptions): ?>
	      <div class="options">
	      <h3 class="productOptions"><?php echo $this->productOptionsLabel; ?></h3>
	      <?php if (!$this->tableless): ?>
			<table cellspacing="0" cellpadding="0" summary="Form fields">
				<?php foreach($product['options'] as $option): ?>
		        	<?php echo $option['html']; ?>
		        <?php endforeach; ?>
			</table>
		  <?php else: ?>
		  	<ul id="optionList" class="optionList">
			<?php foreach($product['options'] as $option): ?>
	        	<li id="option_<?php echo $option['name']; ?>" class="option">
	        		<h4><?php echo $option['description']; ?></h4>
	        		<?php echo $option['html']; ?>
	        	</li>
	        <?php endforeach; ?>
		  	</ul>
		  <?php endif; ?>
		  <div class="clearBoth"></div>
		  </div>
		  <?php endif; ?>	        
	        <?php if($this->useQuantity): ?>
	        <div class="quantity">
	          <label for="quantity_requested"><?php echo $this->qtyLabel; ?></label>
	          <input name="quantity_requested" type="text" size="3" value="1" onblur="if (this.value=='') { this.value='1'; }" onfocus="if (this.value=='1') { this.value=''; this.select(); }" />
	          <span class="labelModifier"><?php echo $this->qtyLabelModifier; ?></span>
	          </div>
	        <?php else: ?>
	          <input type="hidden" name="quantity_requested" value="1" />
	        <?php endif; ?>

	      <div class="productButtons">
	          <?php foreach($this->buttonTypes as $buttonType): ?>
	        		<div style="float: left; padding-right: 5px;"><input type="submit" class="submit button addCart" name="submit" value="Add to Cart" /><?php //echo $this->buttons[$buttonType][$this->productId]; ?></div>
	          <?php endforeach; ?>
	          <?php if ($this->useReg): ?>
	          	<div class="registryLink">
	          		<a href="/registry-manager/action/add_to_registry/aset_id/<?php echo $product['aset_id'] ?>/quantity_requested/1/id/<?php echo $product['id'] ?>.html">Add to Registry</a>
	          	</div>
	          <?php endif; ?>                             
		  </div>
	      <?php if($this->hasMessages): ?>
	      <div class="messages">
	      	<?php foreach($this->messages as $message): ?>
	        	<p class="message"><?php echo $message; ?></p>
	        <?php endforeach; ?>
	      </div>
	      <?php endif; ?>
		  <?php if($this->hasExtraImages): ?>
	      <div class="productGallery">
	      <div class="highslide-gallery">
	        <div class="subImages">
	        <?php foreach($this->extraProductImages as $image): ?>
	        	<?php if($image['has_large_image']): ?>
	            	<noscript>
	                <a href="<?php echo $image['large_image_link']; ?>" title="<?php echo $product['name']; ?>" target="_blank"><img src="<?php echo $image['file_path']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" alt="<?php echo $image['alt']; ?>" class="productImg" /></a>
	                </noscript>
	                <a href="<?php echo $image['large_image_link']; ?>" <?php echo $image['on_thumbnail_click_event']; ?> title="<?php echo $product['name']; ?>"><img src="<?php echo $image['file_path']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" alt="<?php echo $image['alt']; ?>" class="productImg" /></a>
	            <?php else: ?>
	            	<img src="<?php echo $image['file_path']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" alt="<?php echo $image['alt']; ?>" class="productImg" />
	            <?php endif; ?>
	        <?php endforeach; ?>
	        </div>
	        </div>
	        </div>
	        <?php endif; ?>
	      <div class="additionalInformation">
	      
	      </div>
	      	<div class="clearBoth"></div>
	      </div>
		</form>
    </div>
    <div id="tabs" class="column3">
    	<h4 class="infoheader"><?php echo $this->productDescriptionLabel; ?></h4>
        <div class="description">
        	<p><?php echo $product['description']; ?></p>
        </div>
        <div class="continue">
   			<a href="<?php echo $this->referrer; ?>" title="Continue Shopping">Continue Shopping</a>
   		</div>
    </div>
    <?php endforeach; ?>
   	<?php endif; ?>
  </div>
</div>
<?php if ($this->hasError): ?>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.scrollTo(null, ($('<?php echo $this->formId; ?>').getElement('div.error').getPosition().y - 20));
//--><!]]>
</script>
<?php endif; ?>