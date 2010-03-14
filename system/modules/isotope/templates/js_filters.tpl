<script language="javascript" type="text/javascript">
window.addEvent('domready', function() {
			
		var mId = '<?php echo $this->mId; ?>';
		
		var filterForm = $('filterForm');
		
		filterForm.addEvent('submit',function(event){ event.stop(); }); 
		
		var ajaxParams = '<?php echo $this->ajaxParams; ?>';
				
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
				data: 'action=fmd&' + ajaxParams + IsotopeFrontend.getQueryString(intPerPage) + '&clear=1',
				onRequest: IsotopeFrontend.showLoader(),
				onSuccess: function(responseText, responseXML) { IsotopeFrontend.insertProductList(responseText); IsotopeFrontend.hideLoader(); IsotopeFrontend_Lister.modifyPagination(ajaxParams); }
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
				 data: 'action=fmd&' + ajaxParams + IsotopeFrontend.getQueryString($('ctrl_per_page').get('value')),
				 onRequest: IsotopeFrontend.showLoader(),
				 onSuccess: function(responseText, responseXML) { IsotopeFrontend.insertProductList(responseText); IsotopeFrontend.hideLoader(); IsotopeFrontend.loadProductBinders(mId); IsotopeFrontend_Lister.modifyPagination(ajaxParams); }
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
				 data: 'action=fmd&' + ajaxParams + IsotopeFrontend.getQueryString($('ctrl_per_page').get('value')),
				 onRequest: IsotopeFrontend.showLoader(),
				 onSuccess: function(responseText, responseXML) { IsotopeFrontend.insertProductList(responseText); IsotopeFrontend.hideLoader(); IsotopeFrontend.loadProductBinders(mId); IsotopeFrontend_Lister.modifyPagination(ajaxParams); }
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
					 data: 'action=fmd&' + ajaxParams + IsotopeFrontend.getQueryString($('ctrl_per_page').get('value')),
					 onRequest: IsotopeFrontend.showLoader(),
					 onSuccess: function(responseText, responseXML) { IsotopeFrontend.insertProductList(responseText); IsotopeFrontend.hideLoader(); IsotopeFrontend.loadProductBinders(mId); IsotopeFrontend_Lister.modifyPagination(ajaxParams); }
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
				 data: 'action=fmd&' + ajaxParams + IsotopeFrontend.getQueryString($('ctrl_per_page').get('value')),
				 onRequest: IsotopeFrontend.showLoader(),
				 onSuccess: function(responseText, responseXML) { IsotopeFrontend.insertProductList(responseText); IsotopeFrontend.hideLoader(); IsotopeFrontend.loadProductBinders(mId); IsotopeFrontend_Lister.modifyPagination(ajaxParams); }
			 }).send();
			
		 });
		<?php endif; ?>	
		
		
	});
	
</script>