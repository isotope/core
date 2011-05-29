
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formId; ?>" />


<?php if($this->hasSorting): ?>
<div class="sorting">
<label for="ctrl_sorting_<?php echo $this->id; ?>"><?php echo $this->sortingLabel; ?></label>
<select name="sorting" id="ctrl_sorting_<?php echo $this->id; ?>" class="select" onchange="<?php echo $this->formId; ?>.submit();">
<?php foreach( $this->sortingOptions as $option ): ?>
<option value="<?php echo $option['value']; ?>"<?php if($option['default']) echo ' selected="selected"'; ?>><?php echo $option['label']; ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>

<?php if ($this->hasLimit): ?>
<div class="limit">
<label for="ctrl_limit_<?php echo $this->id; ?>"><?php echo $this->limitLabel; ?></label>
<select name="limit" id="ctrl_limit_<?php echo $this->id; ?>" class="select" onchange="<?php echo $this->formId; ?>.submit();">
<?php foreach( $this->limitOptions as $option ): ?>
<option value="<?php echo $option['value']; ?>"<?php if($option['default']) echo ' selected="selected"'; ?>><?php echo $option['label']; ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>


            <?php if($this->filters): ?>
                <input type="hidden" name="filters" value="<?php echo $this->filterFields; ?>" />
                <?php foreach($this->filters as $filter): ?>
                <?php  echo $filter['html']; ?>
                <?php endforeach; ?>
            <?php endif; ?>


            <?php if ($this->searchable): ?>
                <div class="filter_search">
                <label for="ctrl_for"><?php echo $this->keywordsLabel; ?></label>
                <noscript><input type="text" name="for" id="ctrl_for" class="text" value="<?php echo $this->for; ?>" /></noscript>
                <input type="text" name="for" id="ctrl_for" class="text" value="<?php echo ($this->for ? $this->for : $this->defaultSearchText); ?>" onblur="if (this.value=='') { this.value='<?php echo $this->defaultSearchText; ?>'; }" onfocus="if (this.value=='<?php echo $this->defaultSearchText; ?>') { this.value=''; this.select(); }" />
                </div>
            <?php endif; ?>

            <div class="submit_container">
                <button type="submit" name="search" id="ctrl_search"><?php echo $this->searchLabel; ?></button>
            </div>

<a href="<?php echo $this->action; ?>" class="clear_filters"><?php echo $this->clearLabel; ?></a>
<div class="clear">&nbsp;</div>

</div>
</form>
    
</div>
<!-- indexer::continue -->