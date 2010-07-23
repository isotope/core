<div class="<?php echo $this->class; ?>">
<h3><a href="<?php echo $this->href; ?>" title="<?php echo $this->name; ?>">
	<?php echo $this->name; ?><br />
	<?php echo $this->second_party_name; ?>
</a></h3>
<p class="context"><?php if($this->event_type): echo $this->event_type; ?>, <?php endif; ?><?php echo date("m/d/Y",$this->date); ?></p>
</div>