<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if ($this->orderBy): ?>

<div class="filter_order_by">
<form action="<?php echo $this->action; ?>" method="get">
<input type="hidden" name="for" value="<?php echo $this->for; ?>" />
<input type="hidden" name="page" value="<?php echo $this->page; ?>" />
<input type="hidden" name="per_page" value="<?php echo $this->per_page; ?>" />
<select name="order_by" id="ctrl_order_by" class="select">
<?php 	foreach($this->orderBy as $value=>$label): ?>
<option value="<?php echo $value; ?>"<?php echo ($value==$this->order_by ? " selected" : "") ?>><?php echo $label; ?></option>
<?php	endforeach; ?>
</select>
<?php endif; ?>
</form>
</div>

<?php //if ($this->searchable): ?>

<div class="filter_search">
<form action="<?php echo $this->action; ?>" id="searchForm" method="get">
<div class="formbody">
<input type="hidden" name="order_by" value="<?php echo $this->order_by; ?>" />
<input type="hidden" name="page" value="<?php echo $this->page; ?>" />
<input type="hidden" name="per_page" value="<?php echo $this->per_page; ?>" />
<label for="ctrl_for" class="invisible"><?php echo $this->keywordsLabel; ?></label>
<input type="text" name="for" id="ctrl_for" class="text" value="<?php echo $this->for; ?>" />
</div>
<div class="submit_container"><button type="button" name="search" id="ctrl_search"><?php echo $this->searchLabel; ?></button></div>
</form>
</div>
<?php //endif; ?>
<?php if ($this->perPage): ?>

<div class="filter_per_page">
<form action="<?php echo $this->action; ?>" method="get">
<div class="formbody">
<input type="hidden" name="order_by" value="<?php echo $this->order_by; ?>" />
<input type="hidden" name="for" value="<?php echo $this->for; ?>" />
<input type="hidden" name="page" id="ctrl_page" value="<?php echo $this->page; ?>" />
<label for="ctrl_per_page" class="invisible"><?php echo $this->perPageLabel; ?></label>
<select name="per_page" id="ctrl_per_page" class="select">
<?php foreach($this->limit as $row): ?>
  <option value="<?php echo $row; ?>"<?php if ($this->per_page == $row): ?> selected="selected"<?php endif; ?>><?php echo $row; ?></option>
<?php endforeach; ?>
</select>
</div>
</form>
</div>
<?php endif; ?>
<?php if($this->filters): ?>
<?php foreach($this->filters as $filter): ?>
<?php 	echo $filter['html']; ?>
<?php endforeach; ?>
<?php endif; ?>
<div class="clear_filters"><button type="button" name="clear" id="ctrl_clear"><?php echo $this->clearLabel; ?></button></div>
<div class="clear">&nbsp;</div>
</div>

<?php if($this->enableAjax): ?>
<div id="ajaxOverlay" style="display: none;">&nbsp;</div>
<div id="ajaxLoader" class="ctrl_ajax_loader" style="display: none;">
<p>Loading...<br /><?php echo $this->loadingMessage; ?></p>
</div>
<script language="javascript" type="text/javascript">

window.addEvent('domready', function() {
	
	function showLoader()
	{
		$('ajaxOverlay').setStyle('display','block');
		$('ajaxLoader').setStyle('display','block');
	}
	
	function hideLoader()
	{
		$('ajaxOverlay').setStyle('display','none');
		$('ajaxLoader').setStyle('display','none');
	}
	
	function insertProductList(html)
	{
		$('product_list').set('html', html);
	}
	
	function getQueryString()
	{
		return '&order_by=' + $('ctrl_order_by').get('value') + '&per_page=' + $('ctrl_per_page').get('value') + '&for=' + $('ctrl_for').get('value');
	}

	function getPageQueryString($i)
	{
		
		return '&order_by=' + $('ctrl_order_by').get('value') + '&per_page=' + $('ctrl_per_page').get('value') + '&page=' + $('ctrl_page').get('value') + '&for=' + $('ctrl_for').get('value');
	}
		
	function modifyPagination()
	{
		var paginationLinks = $$('div.pagination ul li').getChildren('a');
		var currIndex;
		
		paginationLinks.each(function(item, index){
		
			item.addEvent('click', function(event) {
				event.stop();
				var req = new Request({
					method: 'get',
					url: 'ajax.php',
					data: '<?php echo $this->ajaxParams; ?>' + getQueryString(),
					onRequest: showLoader(),
					onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); modifyPagination(); setPage(); }
				}).send();
			});
			
			item.set('href','#');
			item.set('id','page_' + (index+1));
			
			if(item.hasClass('first'))
			{
				item.set('id','page_1');				
			}
			
			if(item.hasClass('last'))
			{								
				item.set('id','page_' + $('ctrl_last_page').get('value'));				
			}
					
					
		});
	}
		
	var searchForm = $('searchForm');
	
	searchForm.addEvent('submit',function(event){ event.stop(); }); 

	var ctrlClear = $('ctrl_clear');
		
		ctrlClear.addEvent('click', function(event) {
			event.stop();
			
			$('ctrl_for').set('value', '');
			$('ctrl_per_page').set('value',10);
			
			var req = new Request({
				method: 'get',
				url: 'ajax.php',
				data: '<?php echo $this->ajaxParams; ?>',
				onRequest: showLoader(),
				onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); }
			}).send();		
		});

	<?php if($this->orderBy): ?>
	var ctrlOrderBy = $('ctrl_order_by');
    ctrlOrderBy.addEvent('change', function(event) {
         event.stop();
         var req = new Request({
             method: 'get',
             url: 'ajax.php',
             data: '<?php echo $this->ajaxParams; ?>' + getQueryString(),
             onRequest: showLoader(),
             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); modifyPagination(); }
         }).send();
		
     });
    <?php endif; ?>
    
    <?php if($this->searchable): ?>
    var ctrlSearch = $('ctrl_search');
    ctrlSearch.addEvent('click', function(event) {
         event.stop();
         event.preventDefault();
         var req = new Request({
             method: 'get',
             url: 'ajax.php',
             data: '<?php echo $this->ajaxParams; ?>' + getQueryString(),
             onRequest: showLoader(),
             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); modifyPagination(); }
         }).send();
		
     });
     
    var ctrlFor = $('ctrl_for');
    ctrlFor.addEvent('keyup', function(event) {
         if(event.key=='enter')
         {
         	
	         event.stop();
	         var req = new Request({
	             method: 'get',
	             url: 'ajax.php',
	             data: '<?php echo $this->ajaxParams; ?>' + getQueryString(),
	             onRequest: showLoader(),
	             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); modifyPagination(); }
	         }).send();
		 }
     });
    <?php endif; ?>
    
    <?php if($this->perPage): ?>
    var ctrlPerPage = $('ctrl_per_page');
    ctrlPerPage.addEvent('change', function(event) {
         event.stop();
         var req = new Request({
             method: 'get',
             url: 'ajax.php',
             data: '<?php echo $this->ajaxParams; ?>' + getQueryString(),
             onRequest: showLoader(),
             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); modifyPagination(); }
         }).send();
		
     });
    <?php endif; ?>

});
</script>
<?php endif; ?>