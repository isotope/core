<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

    <?php if ($this->headline): ?>
    
    <<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
    <?php endif; ?>
    
    <div class="formbody">
        <form id="filterForm" action="<?php echo $this->action; ?>" method="get">
            <?php if($this->orderBy): ?>
            
                <div class="filter_order_by">
                    <label for="order_by"><?php echo $this->orderByLabel; ?></label>
                    <select name="order_by" id="ctrl_order_by" class="select" onchange="filterForm.submit();">
                    <?php 	foreach($this->orderBy as $value=>$label): ?>
                    <option value="<?php echo $value; ?>"<?php echo ($value==$this->order_by ? " selected" : "") ?>><?php echo $label; ?></option>
                    <?php	endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($this->perPage): ?>
            
                <div class="filter_per_page">
                <label for="per_page"><?php echo $this->perPageLabel; ?></label>
                <select name="per_page" id="ctrl_per_page" class="select" onchange="filterForm.submit();">
                  <option value=""<?php echo (!$this->per_page ? " selected" : ""); ?>>-</option>
                <?php foreach($this->limit as $row): ?>
                  <option value="<?php echo $row; ?>"<?php if ($this->per_page == $row): ?> selected="selected"<?php endif; ?>><?php echo $row; ?></option>
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
                <input type="text" name="for" id="ctrl_for" class="text" value="<?php echo $this->for; ?>" />
                </div>
            <?php endif; ?>
            
            <div class="submit_container">
                <button type="submit" name="search" id="ctrl_search"><?php echo $this->searchLabel; ?></button>
            </div>
        </form>
        <div class="clear_filters">
        <form action="<?php echo $this->baseUrl; ?>" method="post">
            <button type="submit" name="clear" id="ctrl_clear"><?php echo $this->clearLabel; ?></button>
        </form>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
</div>

<?php if($this->script): ?>
<?php echo $this->script; ?>
<?php endif; ?>