<noscript>
<input type="<?php echo $this->buttonFallbackType; ?>" name="button_name_<?php echo $this->buttonFormName; ?>"<?php echo ($this->buttonUsesImage ? ' src="' . $this->buttonImage . '"' : ' value="' . $this->buttonLabel . '"); ?><?php echo ($this->buttonWidth > 0 ? 'width="' . $this->buttonWidth . '" : ''); ?><?php echo ($this->buttonHeight > 0 ? 'height="' . $this->buttonHeight . '" : ''); ?> tabIndex="<?php echo $this->buttonTabIndex : ''); ?>" />
</noscript>
<a href="#" onClick="<?php echo $this->buttonClickEvent; ?>" id="button_id_<?php echo $this->buttonID; ?>" title="<?php echo $this->buttonLabel; ?>" tabIndex="<?php echo $this->buttonTabIndex : ''); ?>"><?php echo $this->buttonLabel; ?></a>

