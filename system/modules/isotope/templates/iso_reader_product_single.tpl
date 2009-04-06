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
<h1><?php echo $this->productName; ?></h1>
<div class="mod_productReader">
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
            	<a href="<?php echo $this->mainImage['large_image_link']; ?>" title="<?php echo $product['product_name']; ?>" target="_blank"><img src="<?php echo $this->mainImage['file_path']; ?>" width="<?php echo $this->mainImage['width']; ?>" height="<?php echo $this->mainImage['height']; ?>" alt="<?php echo $this->mainImage['alt']; ?>" class="productImg" /></a>
            </noscript>
            <a href="<?php echo $this->mainImage['large_image_link']; ?>" <?php echo $this->mainImage['on_thumbnail_click_event']; ?> title="<?php echo $product['product_name']; ?>"><img src="<?php echo $this->mainImage['file_path']; ?>" width="<?php echo $this->mainImage['width']; ?>" height="<?php echo $this->mainImage['height']; ?>" alt="<?php echo $this->mainImage['alt']; ?>" class="productImg" /></a>
        <?php else: ?>
            <img src="<?php echo $this->mainImage['file_path']; ?>" width="<?php echo $this->mainImage['width']; ?>" height="<?php echo $this->mainImage['height']; ?>"  alt="<?php echo $this->mainImage['alt']; ?>" class="productImg" />
        <?php endif; ?></div>
      <!-- <p class="caption"><?php //echo $product['product_name']; ?></p>-->
    </div>
    <div class="column2">
      <h2 class="productName"><?php echo $this->productDetailLabel; ?></h2>
      <div class="pricing">
        <p class="productPrice"><?php echo $product['price_string']; ?></p>
        <p class="sku"><?php echo $product['product_sku']; ?></p>
        <div class="clearBoth"></div>
      </div>
      <?php if(strlen($this->embeddedMedia) > 0): ?>
      <div class="media"><?php echo $this->embeddedMedia; ?></div>
      <?php endif; ?>
      <div class="options">
      	<!--<?php //foreach($this->productOptions as $option): ?>
        
        <?php //endforeach; ?>-->
        <?php if($this->hasOptions): ?>
        <div class="optionSelect">
          <select class="optionsSelect">
            <option>Choose Your Options</option>
          </select>
        </div>
        <?php endif; ?>
        <?php if($this->useQuantity): ?>
        <div class="quantity">
          <input name="qty" type="text" size="3" />
          Qty </div>
        <?php endif; ?>
      </div>
      <div class="productButtons">
          <?php foreach($this->buttonTypes as $buttonType): ?>
        		<div style="float: left; padding-right: 5px;"><?php echo $this->buttons[$buttonType][$this->productId]; ?></div>
          <?php endforeach; ?>
          <?php if ($this->useReg): ?>
          	<div class="registryLink">
          		<a href="/registry-manager/action/add_to_registry/aset_id/<?php echo $product['aset_id'] ?>/quantity_requested/1/id/<?php echo $product['product_id'] ?>.html">Add to Registry</a>
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
                <a href="<?php echo $image['large_image_link']; ?>" title="<?php echo $product['product_name']; ?>" target="_blank"><img src="<?php echo $image['file_path']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" alt="<?php echo $image['alt']; ?>" class="productImg" /></a>
                </noscript>
                <a href="<?php echo $image['large_image_link']; ?>" <?php echo $image['on_thumbnail_click_event']; ?> title="<?php echo $product['product_name']; ?>"><img src="<?php echo $image['file_path']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" alt="<?php echo $image['alt']; ?>" class="productImg" /></a>
            <?php else: ?>
            	<img src="<?php echo $image['file_path']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" alt="<?php echo $image['alt']; ?>" class="productImg" />
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
        </div>
        <?php endif; ?>
      <div class="additionalInformation">
      
      </div>
      	<div class="clearBoth"></div>
      </div>
    </div>
    <div class="column3">
    	<h4 class="descriptionHeader"><?php echo $this->productDescriptionLabel; ?></h4>
        <div class="description">
        	<p><?php echo $product['product_description']; ?></p>
        </div>
    </div>
    <?php endforeach; ?>
   	<?php endif; ?>
  </div>
</div>