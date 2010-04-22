<script type="text/javascript">
<!--//--><![CDATA[//><!--
var IsotopeFrontend_Lister =
{	
	modifyPagination: function(ajaxParams)
	{
		var paginationLinks = $$('div.pagination ul li').getChildren('a');		
		paginationLinks.each(function(item, index){
			var qString = item.get('href').toString();
			var pageNum = Isotope.gup('page',qString);
			item.set('href','#');			
			item.addEvent('click', function(event) {
				event.stop();
				var req = new Request({
					method: 'get',
					url: 'ajax.php',
					urlencoded: true,
					data: 'action=fmd&' + ajaxParams + Isotope.getQueryString($('ctrl_per_page').get('value')) + IsotopeFrontend_Lister.setPage(pageNum),
					onRequest: Isotope.displayBox('Loading data …'),
					onSuccess: function(responseText, responseXML) { Isotope.insertProductList(responseText); IsotopeFrontend_Lister.modifyPagination(ajaxParams); Isotope.hideBox(); }
				}).send();
			});		
		});
	},
	setPage: function(i){
		return '&page=' + i;	
	}
}
window.addEvent('domready', function() {
	IsotopeFrontend_Lister.modifyPagination('<?php echo $this->ajaxParams; ?>');
});
//--><!]]>
</script>