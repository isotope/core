
<h1 id="tl_welcome"><?php echo $this->welcome; ?></h1>

<div id="tl_soverview">

<div id="tl_moverview">
<?php foreach ($this->arrGroups as $strGroup=>$arrModules): ?>

<h2><?php echo $strGroup; ?></h2>
<?php foreach ($arrModules as $strModule=>$arrModule): ?>

<div class="tl_module_desc">
<h3><a href="<?php echo $this->script; ?>?do=isotope&table=<?php echo $strModule; ?>" class="navigation <?php echo $strModule; ?>"<?php if ($arrModule['icon']): ?> style="background-image:url('<?php echo $arrModule['icon']; ?>')"<?php endif; ?>><?php echo $arrModule['name']; ?></a></h3>
<?php echo $arrModule['description']; ?> 
</div>
<?php endforeach; endforeach; ?>
</div>

</div>
