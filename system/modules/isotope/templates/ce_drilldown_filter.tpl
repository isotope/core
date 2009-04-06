<!-- indexer::stop -->
<?php foreach($this->filterGroup as $group): ?>
<?php if($this->filterType=="select"): ?>
<select name="<?php echo $group['filterName']; ?>">
	<?php foreach($groups['filterValues'] as $optionValue): ?>
    	<option value="<?php echo $optionValue['value']; ?>"><?php echo ($currVal==$optionValue['value'] ? " selected" : ""); ?>>$optionValue['label']</option>
    <?php endforeach; ?>
</select>
<?php else: ?>

<?php endif; ?>
<?php endforeach; ?>

<!-- indexer::continue -->

