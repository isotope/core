
<div class="registry_full">

<div class="info">
<h2><?php echo $this->registryTitle; ?></h2>
<table cellpadding="10" cellspacing="0" summary="Gift Registry Info"><tr>
<td><strong><?php echo $this->name; ?><br /><?php echo $this->second_party_name; ?></strong></td>
<td><?php echo date("m/d/Y",$this->date); ?></td>
<td><?php echo $this->event_type; ?></td>
<td><a class="edit" href="<?php echo $this->editLink; ?>"><span><?php echo $this->editText; ?></span></a></td>
</tr><tr><td colspan="4"><p><?php echo $this->description; ?></p></td>
</tr></table>
</div>

<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<table cellpadding="0" cellspacing="0" summary="Gift Registry">
<tbody>
<?php foreach($this->products as $product): ?>
    <tr class="<?php echo $product['class']; ?>">
		<td class="col_0 col_first image"><a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><img src="<?php echo $product['image']['gallery']; ?>" alt="<?php echo $product['image']['alt']; ?>" class="thumbnail"<?php echo $product['image']['gallery_size']; ?> /></a></td>
   		<td class="col_1 name">
   			<a href="<?php echo $product['link']; ?>" title="<?php echo $product['name']; ?>"><?php echo $product['name']; ?></a>
			<?php if($product['product_options']): ?>
			<div class="optionswrapper">
				<ul class="productOptions">
				<?php foreach($product['product_options'] as $option): ?>
					<li><strong><?php echo $option['name']; ?>:</strong> <?php echo implode(', ', $option['values']); ?></li>
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</td>
		<td class="col_2 quantity"><input name="quantity[<?php echo $product['registry_item_id']; ?>]" size="3" type="text" class="text" value="<?php echo $product['quantity']; ?>" maxlength="3" /></td>
   		<td class="col_3 price"><?php echo $product['price']; ?></td>
    	<td class="col_4 price total"><?php echo $product['total_price']; ?></td>
    	<td class="col_5 tax"><?php echo $product['tax_id']; ?></td>
    	<td class="col_6 col_last remove"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>" class="remove"><?php echo $product['remove_link_text']; ?></a></td>
	</tr>
<?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="submit_container">
	<input type="submit" class="submit update" value="<?php echo $GLOBALS['TL_LANG']['MSC']['updateRegistryBT']; ?>" />
	<a class="continue" href="<?php echo $this->continueJumpTo; ?>"><span><?php echo $this->continueJumpToLabel; ?></span></a>
</div>
</form>
</div>