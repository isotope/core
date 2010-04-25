<?php if (is_array($this->products) && count($this->products)): ?>
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php foreach( $this->products as $product ): ?>
<div class="<?php echo $product['class']; ?>">
<?php echo $product['html']; ?>
</div>
<?php endforeach; ?>
<?php echo $this->pagination; ?>
</div>
<?php endif; ?>