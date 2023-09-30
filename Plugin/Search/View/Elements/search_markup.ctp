<?php
$this->extend('admin/block');

echo $this->fetch('searchButtons');
?>
<div id="extend-filter" class="<?php echo $className; ?> filter row-fluid extend-filter well">
	<?php echo $this->fetch('searchForm');?>
	<?php echo $this->fetch('searchColumns');?>
	<div class="clearfix"></div>
	<div class="actions">
		<div class="filter-bottom">
			<?php echo $this->fetch('searchBottom');?>
		</div>
		<?php echo $this->fetch('searchActions');?>
	</div>
	<div class="clearfix"></div>
	<?php echo $this->fetch('searchFormEnd');?>
</div>
<?php echo $this->fetch('searchSmallerForm'); ?>
<?php echo $this->fetch('searchAfter');?>
<?php
if (CakePlugin::loaded('Locations'))
{
    echo $this->Html->script(['Locations.Location/edit'], array('inline' => false));
}

echo $this->Html->script(['/libraries/chosen/chosen.jquery.min.js',], array('inline' => false));
echo $this->Html->script(['/agency/js/jquery.session', 'Search.filter.js'], array('inline' => true));
?>

