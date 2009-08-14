<!-- indexer::stop -->
<div class="grid <?php echo $this->class; ?><?php echo $this->cssID; ?>"<?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
  <div class="listingHeadline"><h1><?php echo $this->headline; ?></h1></div>
  <!-- START HEADER-->
  <div class="listingHeader">
    <!-- START FILTER-->
    <div class="filterWrap">
        <form action="<?php echo $this->action; ?>" method="get" name="frmFilters">
        <div class="formbody">
        <!-- <input type="hidden" name="ignore_page_id" value="<?php echo $this->ignore_page_id; ?>" /> -->
        <!-- <input type="hidden" name="pas_id" value="<?php echo $this->pas_id; ?>" /> -->
        <div class="searchfields">       
       &nbsp;<label for="<?php echo $this->searchFilterText['name']; ?>"><?php echo $this->searchFilterText['label']; ?></label><input type="<?php echo $this->searchFilterText['type']; ?>" name="<?php echo $this->searchFilterText['name']; ?>" class="text" value="<?php echo $this->searchFilterText['current_value']; ?>" />&nbsp;<input type="submit" name="submit" value="Submit" />
       </div>
        <div class="filters">
      	<?php if(sizeof($this->filters)): ?>
      	<?php foreach($this->filters as $filter): ?>
      		<label for="<?php echo $filter['name']; ?>"><?php echo $filter['label']; ?></label>
      		<?php //switch($filter['type'])  
      			  //{ ?>
      		<?php   //default: ?>
			      		<select name="<?php echo $filter['name']; ?>" class="select" onchange="form.submit();">
			      			<?php foreach($filter['options'] as $option): ?>
			      				<option value="<?php echo $option['value']; ?>"<?php if($filter['current_value']==$option['value']): ?> selected="selected"<?php endif; ?>><?php echo $option['label']; ?></option>
			      			<?php endforeach; ?>
			      		</select>
      		<?php 	//	break; ?>
      		<?php //} ?>
      	<?php endforeach; ?> 
      	<?php endif; ?>
      	</div>
      	<div class="orderby">
      	<label for="order_by"><?php echo $this->labelOrderBy; ?></label>
        <select name="order_by" class="select" onchange="form.submit();">
        	<option value=""<?php if($this->order_by==$option['value']): ?> selected="selected"<?php endif; ?>>(select)</option>
            <?php foreach($this->orderOptions as $option): ?>
                <option value="<?php echo $option['value']; ?>"<?php if($this->order_by==$option['value']): ?> selected="selected"<?php endif; ?>><?php echo $option['label']; ?></option>
            <?php endforeach; ?>
        </select>
        </div>
        <div class="perpage">
        <label for="per_page"><?php echo $this->labelPerPage; ?></label>
        <select name="per_page" class="select" onchange="form.submit();">
          <?php foreach($this->perPageOptions as $option): ?>
          	<option value="<?php echo $option; ?>"<?php if ($this->per_page == $option): ?> selected="selected"<?php endif; ?>><?php echo $option; ?></option>
          <?php endforeach; ?>
          <!--<option value="250"<?php if ($this->per_page == 250): ?> selected="selected"<?php endif; ?>>250</option>
          <option value="500"<?php if ($this->per_page == 500): ?> selected="selected"<?php endif; ?>>500</option>-->
        </select>
        </div>
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
                <div class="col1 productInfo"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['thumbnail'] ?>" alt="<?php echo $product['name']; ?>" border="0" class="productThumb" /></a>
                  <h3 class="productName"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['name']; ?></a></h3>
                   <p class="productPrice"><?php echo $product['price_string']; ?></p>
                  <?php if($this->showTeaser && $product['teaser']): ?>
                 <p class="productTeaser"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['teaser']; ?></a></p> 
                  <?php endif; ?>
                  
                </div>
                <div class="col2 productButtons">
                  <a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>">View Details</a>
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