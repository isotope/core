<!-- indexer::stop -->
<div class="mod_productListing grid <?php echo $this->class; ?><?php echo $this->cssID; ?>"<?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
  <div class="listingHeadline"><h1><?php echo $this->headline; ?></h1></div>
  <!-- START HEADER-->
  <div class="listingHeader">
    <!-- START FILTER-->
    <div class="filterWrap">
        <form action="<?php echo $this->action; ?>" method="get">
        <div class="formbody">
        <input type="hidden" name="ignore_page_id" value="<?php echo $this->ignore_page_id; ?>" />
        <input type="hidden" name="pas_id" value="<?php echo $this->pas_id; ?>" />
      	<?php foreach($this->filters as $filter): ?>
      		<label for="<?php echo $filter['name']; ?>"><?php echo $filter['label']; ?></label>
      		<select name="<?php echo $filter['name']; ?>" class="select" onchange="form.submit();">
      			<?php foreach($filter['options'] as $option): ?>
      				<option value="<?php echo $option['value']; ?>"<?php if($filter['current_value']==$option['value']): ?> selected="selected"<?php endif; ?>><?php echo $option['label']; ?></option>
      			<?php endforeach; ?>
      		</select>
      	<?php endforeach; ?> 
      	<label for="order_by"><?php echo $this->labelOrderBy; ?></label>
        <select name="order_by" class="select" onchange="form.submit();">
        	<option value=""<?php if($this->order_by==$option['value']): ?> selected="selected"<?php endif; ?>>(select)</option>
            <?php foreach($this->orderOptions as $option): ?>
                <option value="<?php echo $option['value']; ?>"<?php if($this->order_by==$option['value']): ?> selected="selected"<?php endif; ?>><?php echo $option['label']; ?></option>
            <?php endforeach; ?>
        </select>
        
        
        <label for="per_page"><?php echo $this->labelPerPage; ?></label>
        <select name="per_page" class="select" onchange="form.submit();">
          <?php foreach($this->perPageOptions as $option): ?>
          	<option value="<?php echo $option; ?>"<?php if ($this->per_page == $option): ?> selected="selected"<?php endif; ?>><?php echo $option; ?></option>
          <?php endforeach; ?>
          <!--<option value="250"<?php if ($this->per_page == 250): ?> selected="selected"<?php endif; ?>>250</option>
          <option value="500"<?php if ($this->per_page == 500): ?> selected="selected"<?php endif; ?>>500</option>-->
        </select>
        </div>
        </form>
    </div>
    <!-- END FILTER-->
    <div class="clearBoth"></div>
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
                <div class="col1 productInfo"><a href="<?php echo $product['product_link']; ?>" title="<?php echo $product['product_name']; ?>"><img src="<?php echo $product['thumbnail'] ?>" alt="<?php echo $product['product_name']; ?>" border="0" class="productThumb" /></a>
                  <h3 class="productName"><a href="<?php echo $product['product_link']; ?>" title="<?php echo $product['product_name']; ?>"><?php echo $product['product_name']; ?></a></h3>
                   <p class="productPrice"><?php echo $product['price_string']; ?></p>
                  <?php if($product['product_teaser']): ?>
                 <p class="productTeaser"><a href="<?php echo $product['product_link']; ?>" title="<?php echo $product['product_name']; ?>"><?php echo $product['product_teaser']; ?></a></p>
                  <?php endif; ?>
                  
                </div>
                <div class="col2 productButtons">
                  <?php foreach($this->buttonTypes as $buttonType): ?>
                		<div style="float: left; padding-right: 5px;"><?php echo $this->buttons[$buttonType][$product['product_id']]; ?></div>
                  <?php endforeach; ?>
                  <?php if ($this->useReg): ?>
                  	<div class="registryLink">
                  		<a href="/registry-manager/action/add_to_registry/aset_id/<?php echo $product['aset_id'] ?>/quantity_requested/1/id/<?php echo $product['product_id'] ?>.html">Add to Registry</a>
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