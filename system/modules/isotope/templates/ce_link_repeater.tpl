<div class="linkRepeater">
<?php foreach($this->links as $link): ?>
<div class="linkBox">
	<a href="<?php echo $link['url']; ?>" title="<?php echo $link['title']; ?>"><?php echo $link['title']; ?></a>
</div>
<?php endforeach; ?>
</div>