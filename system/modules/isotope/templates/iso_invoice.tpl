<div id="invoice" style="margin:18px; padding:0px; font-size: 62.5%; font-family: Arial, Helvetica, sans-serif; width:800px; border:solid 1px #000000; padding-left25px; padding-right:25px;">
	<table id="header" cellpadding="5" cellspacing="0" border="0" width="100%">
        <tr>
        <?php if($this->logoImage): ?>
		<td id="logo"><?php echo $this->logoImage; ?></td><?php endif; ?>
		<td style="text-align:right;"><p style="font-size:1.2em; margin-top:0px; margin-bottom:10px; padding:0px;"><?php echo $this->invoiceTitle; ?></p></td>
		</tr>
	</table>
		
	<h2><?php echo $this->orderDetailsHeadline; ?></h2>
	
	<?php foreach( $this->info as $type => $data ): ?>
	<div class="info_container <?php echo $type . $data['class']; ?>">
		<h3><?php echo $data['headline']; ?></h3>
		<div class="info"><?php echo $data['info']; ?></div>
	</div>
	<?php endforeach; ?>
	<div class="clear">&nbsp;</div>
	
	<table cellspacing="0" cellpadding="0" summary="Order items">
		<tbody>
	<?php foreach( $this->items as $item ): ?>
			<tr>
				<td class="col_first col_0 name"><?php if (strlen($item['href'])): ?><a href="<?php echo $item['href']; ?>"><?php endif; echo $item['name']; if (strlen($item['href'])): ?></a><?php endif; ?>
					<?php if(is_array($item['product_options']) && count($item['product_options'])): ?>
					<div class="optionswrapper">
						<ul class="productOptions">
						<?php foreach($item['product_options'] as $option): ?>
							<li><strong><?php echo $option['label']; ?>:</strong> <?php echo $option['value']; ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</td>
				<td class="col_1 quantity"><?php echo $item['quantity']; ?></td>
				<td class="col_2 price"><?php echo $item['price']; ?></td>
				<td class="col_3 price total"><?php echo $item['total']; ?></td>
		    	<td class="col_4 col_last tax"><?php echo $product['tax_id']; ?></td>
			</tr>
	<?php endforeach; ?>
			<tr class="subtotal foot_first">
				<td class="col_0"></td>
				<td class="col_1 name" colspan="2"><?php echo $this->subTotalLabel; ?></td>
				<td class="col_2 price">&nbsp;</td>
				<td class="col_3 price total"><?php echo $this->subTotalPrice; ?></td>
				<td class="col_4 col_last tax">&nbsp;</td>
			</tr>
	<?php if (is_array($this->surcharges)): foreach( $this->surcharges as $surcharge ): ?>
			<tr>
				<td class="col_0"></td>
				<td class="col_first name" colspan="2"><?php echo $surcharge['label']; ?></td>
				<td class="price"><?php echo $surcharge['price']; ?></td>
				<td class="price total"><?php echo $surcharge['total_price']; ?></td>
				<td class="col_last tax"><?php echo $surcharge['tax_id']; ?></td>
			</tr>
	<?php endforeach; endif; ?>
			<tr class="grandtotal foot_last">
				<td class="col_0"></td>
				<td class="col_first name" colspan="2"><?php echo $this->grandTotalLabel; ?></td>
				<td class="price total" colspan="2"><?php echo $this->grandTotal; ?></td>
				<td class="col_last tax">&nbsp;</td>
			</tr>
		</tbody>
	</table>
	
	
	<?php if (count($this->downloads)): ?>
	<h2><?php echo $this->downloadsLabel; ?></h2>
	<?php foreach( $this->downloads as $download ): ?>
	<div class="download"><?php if ($download['downloadable']): ?><a href="<?php echo $download['href']; ?>" /><?php endif; echo $download['title']; if ($download['downloadable']): ?></a><?php endif; echo $download['remaining']; ?></div>
	<?php endforeach; endif; ?>	
</div>