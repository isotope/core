
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<ul>
<?php foreach( $this->configs as $config ): if ($config['active']): ?>
	<li class="<?php echo trim('active ' . $config['class']); ?>"><span class="active"><?php echo $config['label']; ?></span></li>
<?php else: ?>
	<li<?php if(strlen($config['class'])): ?> class="<?php echo $config['class']; ?>"<?php endif; ?>><a href="<?php echo $config['href']; ?>"><?php echo $config['label']; ?></a></li>
<?php endif; endforeach; ?>
</ul>

</div>
<!-- indexer::continue -->