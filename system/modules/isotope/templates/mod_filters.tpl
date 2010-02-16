<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
    <?php if ($this->headline): ?>
    
    <<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
    <?php endif; ?>
    
    <div class="formbody">
        <form id="filterForm" action="<?php echo $this->action; ?>" method="<?php echo (!$this->disableAjax ? "get" : "post"); ?>">            
            <?php if($this->orderBy): ?>
                <div class="filter_order_by">
                    <select name="order_by" id="ctrl_order_by" class="select">
                    <?php 	foreach($this->orderBy as $value=>$label): ?>
                    <option value="<?php echo $value; ?>"<?php echo ($value==$this->order_by ? " selected" : "") ?>><?php echo $label; ?></option>
                    <?php	endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <?php if ($this->searchable): ?>
                <div class="filter_search">
                <label for="ctrl_for" class="invisible"><?php echo $this->keywordsLabel; ?></label>
                <input type="text" name="for" id="ctrl_for" class="text" value="<?php echo $this->for; ?>" />
                </div>
            <?php endif; ?>
            
            <?php if ($this->perPage): ?>            	
                <div class="filter_per_page">
                <label for="ctrl_per_page" class="invisible"><?php echo $this->perPageLabel; ?></label>
                <select name="per_page" id="ctrl_per_page" class="select">
                  <option value=""<?php echo (!$this->per_page ? " selected" : ""); ?>>-</option>
                <?php foreach($this->limit as $row): ?>
                  <option value="<?php echo $row; ?>"<?php if ($this->per_page == $row): ?> selected="selected"<?php endif; ?>><?php echo $row; ?></option>
                <?php endforeach; ?>
                </select>
                </div>
            <?php endif; ?>
            
            <?php if($this->filters): ?>
                <?php foreach($this->filters as $filter): ?>
                <?php 	echo $filter['html']; ?>
                <?php endforeach; ?>
            <?php endif; ?>
                <div class="submit_container">
                    <button type="<?php echo (!$this->disableAjax ? "button" : "submit"); ?>" name="search" id="ctrl_search"><?php echo $this->submitLabel; ?></button>
                </div>
                <?php if(!$this->disableAjax): ?>        	 
                 <div class="clear_filters">
                    <button type="button" name="clear" id="ctrl_clear"><?php echo $this->clearLabel; ?></button>
                 </div>
                <?php endif; ?>
        </form>
        <?php if($this->disableAjax): ?>
        <div class="clear_filters">
        <form action="<?php echo $this->baseUrl; ?>" method="<?php echo (!$this->disableAjax ? "get" : "post"); ?>">
            <button type="submit" name="clear" id="ctrl_clear"><?php echo $this->clearLabel; ?></button>
        </form>
        </div>
        <?php endif; ?>
        <div class="clear">&nbsp;</div>
    </div>
</div>

<?php if(!$this->disableAjax): ?>
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
		modifyPagination();
	}
		
	function getQueryString($perPage)
	{
		var keyword = $('ctrl_for').get('value').toString();
		
		return '&order_by=' + $('ctrl_order_by').get('value') + '&for=' + keyword.replace('%', '') + '&per_page=' + $perPage;
	}
	
	function setPage($i)
	{
		
		return '&page=' + $i;
	}
			
	function gup( name, url )
	{
	  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	  var regexS = "[\\?&]"+name+"=([^&#]*)";
	  var regex = new RegExp( regexS );
	  var results = regex.exec( url );
	  if( results == null )
		return "";
	  else
		return results[1];
	}
	
	function modifyPagination()
	{
		var paginationLinks = $$('div.pagination ul li').getChildren('a');
				
		paginationLinks.each(function(item, index){
			
			var qString = item.get('href').toString();
			
			var pageNum = gup('page',qString);
			
			item.set('href','#');			
			
			item.addEvent('click', function(event) {
				event.stop();
				var req = new Request({
					method: 'get',
					url: 'ajax.php',
					urlencoded: true,
					data: '<?php echo $this->ajaxParams; ?>' + getQueryString($('ctrl_per_page').get('value')) + setPage(pageNum),
					onRequest: showLoader(),
					onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); }
				}).send();
			});		
					
		});
	}
	
	var filterForm = $('filterForm');
	
	filterForm.addEvent('submit',function(event){ event.stop(); }); 
	
	modifyPagination();
	
	var ctrlClear = $('ctrl_clear');
		
		ctrlClear.addEvent('click', function(event) {
			event.stop();

			var intPerPage = <?php echo ($this->per_page ? $this->per_page : "10") ?>;		

			$('ctrl_for').set('value', '');
			$('ctrl_order_by').set('value', '');
			$('ctrl_per_page').set('value',intPerPage);
			
			var req = new Request({
				method: 'get',
				url: 'ajax.php',
				urlencoded: true,
				data: '<?php echo $this->ajaxParams; ?>' + getQueryString(intPerPage) + '&clear=1',
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
			 urlencoded: true,
             data: '<?php echo $this->ajaxParams; ?>' + getQueryString($('ctrl_per_page').get('value')),
             onRequest: showLoader(),
             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); }
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
			 urlencoded: true,
             data: '<?php echo $this->ajaxParams; ?>' + getQueryString($('ctrl_per_page').get('value')),
             onRequest: showLoader(),
             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); }
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
				 urlencoded: true,
	             data: '<?php echo $this->ajaxParams; ?>' + getQueryString($('ctrl_per_page').get('value')),
	             onRequest: showLoader(),
	             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); }
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
			 urlencoded: true,
             data: '<?php echo $this->ajaxParams; ?>' + getQueryString($('ctrl_per_page').get('value')),
             onRequest: showLoader(),
             onSuccess: function(responseText, responseXML) { insertProductList(responseText); hideLoader(); }
         }).send();
		
     });
    <?php endif; ?>
	
	

});
</script>
<?php endif; ?>