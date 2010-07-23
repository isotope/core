<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<div id="product_list">

<?php foreach( $this->products as $product ): ?>
<div class="product <?php echo $product['class']; ?>">
<?php echo $product['html']; ?>
</div>
<?php endforeach; ?>
</div>
</div>