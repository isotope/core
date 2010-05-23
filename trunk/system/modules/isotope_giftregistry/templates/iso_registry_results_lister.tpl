<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> iso_search_lister block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<?php if ($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php echo $this->form; ?>

<?php if ($this->header): ?>
<p class="header"><?php echo $this->header; ?></p>
<?php endif; ?>

<?php echo $this->results . $this->pagination; ?>

</div>
<!-- indexer::continue -->