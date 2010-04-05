
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<ul>
<?php foreach( $this->stores as $store ): if ($store['active']): ?>
	<li class="<?php echo trim('active ' . $store['class']); ?>"><span class="active"><?php echo $store['label']; ?></span></li>
<?php else: ?>
	<li<?php if(strlen($store['class'])): ?> class="<?php echo $store['class']; ?>"<?php endif; ?>><a href="<?php echo $store['href']; ?>"><?php echo $store['label']; ?></a></li>
<?php endif; endforeach; ?>
</ul>

</div>
<!-- indexer::continue -->