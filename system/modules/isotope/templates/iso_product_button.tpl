<!-- indexer::stop -->
<?php if($this->buttonType=="link"): ?>

	<?php if($this->isAjaxEnabledButton): ?>

		<noscript>
			<a href="<?php echo $this->actionLink; ?>" title="<?php echo $this->actionTitle; ?>"><?php echo $this->buttonLabelOrImage; ?></a>
		</noscript>

	<?php endif; ?>
	
	<a href="<?php echo ($this->isAjaxEnabledButton ? '#" onClick="' . $this->buttonClickEvent . '"' : $this->actionLink . '"'); ?> id="<?php echo $this->buttonID; ?>" title="<?php echo $this->actionTitle; ?>" tabIndex="<?php echo (strlen($this->buttonTabIndex) > 0 ? $this->buttonTabIndex : '0'); ?>"><?php echo $this->buttonLabelOrImage; ?></a>

<?php endif; ?>
<!-- indexer::start -->
