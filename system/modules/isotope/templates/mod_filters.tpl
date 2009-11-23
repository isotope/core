<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if ($this->searchable): ?>

<div class="list_search">
<form action="<?php echo $this->action; ?>" method="get">
<div class="formbody">
<input type="hidden" name="order_by" value="<?php echo $this->order_by; ?>" />
<input type="hidden" name="sort" value="<?php echo $this->sort; ?>" />
<input type="hidden" name="per_page" value="<?php echo $this->per_page; ?>" />
<label for="ctrl_for" class="invisible"><?php echo $this->keywords_label; ?></label>
<input type="text" name="for" id="ctrl_for" class="text" value="<?php echo $this->for; ?>" />
<input type="submit" class="submit" value="<?php echo $this->search_label; ?>" />
</div>
</form>
</div>
<?php endif; ?>
<?php if ($this->per_page): ?>

<div class="list_per_page">
<form action="<?php echo $this->action; ?>" method="get">
<div class="formbody">
<input type="hidden" name="order_by" value="<?php echo $this->order_by; ?>" />
<input type="hidden" name="for" value="<?php echo $this->for; ?>" />
<label for="ctrl_per_page" class="invisible"><?php echo $this->per_page_label; ?></label>
<select name="per_page" id="ctrl_per_page" class="select">
<?php foreach($this->limit as $row): ?>
  <option value="<?php echo $row; ?>"<?php if ($this->per_page == $row): ?> selected="selected"<?php endif; ?>><?php echo row; ?></option>
<?php endforeach; ?>
</select>
<input type="submit" class="submit" value="<?php echo $this->per_page_label; ?>" />
</div>
</form>
</div>
<?php endif; ?>
<?php foreach($this->filters as $filter): ?>
<?php 	echo $filter['html']; ?>
<?php endforeach; ?>
</div>