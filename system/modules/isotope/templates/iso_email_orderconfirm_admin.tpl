<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css" media="all">

.wrapper {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
}

.hTwo {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	color: gray;
}

.hThree {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: gray;
}

.totals {
	text-align: right;
	font-weight: bold;
}

</style>
</head>
<body>

<table class="wrapper" cellpadding="0" cellspacing="10" border="0">

<tr>
	<td colspan="2">LOGO</td>
</tr>

<tr>
	<td colspan="2">MESSAGE TEXT</td>
</tr>

<tr>
	<td>
		<h2 class="hTwo">BILLING ADDRESS</h2>
		<p>
			Billing Address Info<br />
			Billing Address Info<br />
			Billing Address Info<br />
			Billing Address Info<br />
		</p>
	</td>
	
	
	<td>
		<h2 class="hTwo">SHIPPING ADDRESS</h2>
		<p>
			Billing Address Info<br />
			Billing Address Info<br />
			Billing Address Info<br />
			Billing Address Info<br />
		</p>
	
	</td>
</tr>

<tr>
	<td colspan="2">
		<h2 class="hTwo">ORDER SUMMARY</h2>
		<table class="products" cellpadding="0" cellspacing="10" border="0">
		<tr>
				<td class="header"><h3 class="hThree">Product Name</h3></td>
				<td class="header"><h3 class="hThree">Product Price</h3></td>
				<td class="header"><h3 class="hThree">Product Quantity</h3></td>
				<td class="header"><h3 class="hThree">Product Price</h3></td>
				<td class="header"><h3 class="hThree">Subtotal</h3></td>
		</tr>
		
		
		<!-- FOREACH PRODUCT-->
			<tr>
				<td>Product Name</td>
				<td>Product Price</td>
				<td>Product Quantity</td>
				<td>Product Price</td>
				<td>Product SubTotal</td>
			</tr>
		<!--END FOREACH PRODUCT-->
		
		<tr>
				<td class="totals" colspan="4">Subtotal</td>
				<td>$X.XX</td>
		</tr>
		
		<tr>
				<td class="totals" colspan="4">Tax</td>
				<td>$X.XX</td>
		</tr>
		
		<tr>
				<td class="totals" colspan="4">Total Sale:</td>
				<td>$X.XX</td>
		</tr>
		
		</table>
	</td>
</tr>

<tr>
	<td colspan="2">MESSAGE CLOSING</td>
</tr>

</table>
</body>
</html>