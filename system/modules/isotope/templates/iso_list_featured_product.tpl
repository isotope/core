<!-- indexer::stop -->
<div class="grid featured <?php echo $this->class; ?><?php echo $this->cssID; ?>"<?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
  <!--<div class="listingHeadline"><h1><?php echo $this->headline; ?></h1></div>-->
  <!-- START HEADER-->
  <div class="listingHeader">
    <!-- START PAGER-->
    <div class="pagerWrap">
      <!--<div class="pagerText"><?php echo $this->labelPagerSectionTitle; ?></div>-->
      <div class="pager"><?php echo $this->pagination; ?></div>
    </div>
    <!-- END PAGER-->

    <div class="clearBoth"></div>
  </div>
  <!-- END HEADER-->
  <!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx START PRODUCT LISTING -->
  <div class="listWrapper">
  <?php if($this->noProducts): ?>
    <div class="alertMessage">
        <h3><?php echo $this->messages['noProducts']; ?></h3>
    </div>
  <?php else: ?>
        <?php $i=1; ?>
      <?php foreach($this->products as $product): ?>
   
            <!-- BEGIN PRODUCT-->
            <div class="productWrapper">
                <div class="col1 productInfo"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['thumbnail'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="productThumb" /></a>
                  <h3 class="productName"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['name']; ?></a></h3>
                   <p class="productPrice"><?php echo $product['price_string']; ?></p>
                  <?php if($product['teaser']): ?>
                 <p class="productTeaser"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['teaser']; ?></a></p>
                  <?php endif; ?>
                  
                </div>
                <div class="col2 productButtons">
                  <?php foreach($this->buttonTypes as $buttonType): ?>
                		<div style="float: left; padding-right: 5px;"><?php echo $this->buttons[$buttonType][$product['id']]; ?></div>
                  <?php endforeach; ?>
                  <?php if ($this->useReg): ?>
                  	<div class="registryLink">
                  		<a href="/registry-manager/action/add_to_registry/aset_id/<?php echo $product['aset_id'] ?>/quantity_requested/1/id/<?php echo $product['id'] ?>.html">Add to Registry</a>
                  	</div>
                  <?php endif; ?>
                </div>
                <div class="clearBoth"></div>
            </div>
            <!-- END PRODUCT-->
        <?php if($i == $this->columnLimit): ?>
            <?php $i=0; ?>
            <div class="clearBoth divider"></div>
        <?php endif; ?>
        <?php $i++; ?>
      <?php endforeach; ?>
  <?php endif; ?>
  <div class="clearBoth"></div>
  </div>
  <!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx END PRODUCT LISTING -->
  <!-- START FOOTER-->
  <div class="listingFooter">
    <!-- START PAGER-->
    <div class="pagerWrap">
      <!--<div class="pagerText"><?php echo $this->labelPagerSectionTitle; ?></div>-->
      <div class="pager">
        <?php echo $this->pagination; ?>
      </div>
    </div>
    <!-- END PAGER-->
    <div class="clearBoth"></div>
  </div>
  <!-- END FOOTER-->
</div>
<!-- indexer::continue -->