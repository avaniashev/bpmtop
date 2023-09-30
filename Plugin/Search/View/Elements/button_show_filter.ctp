<?php /** @var View $this */

$icon = $this->Html->tag('i', '', ['class' => 'icon-filter']);

if (! empty($this->request->query)){
	$activeFilterClass = ' filter-is-active';
}
else {
	$activeFilterClass ='';
}

echo $this->Html->div('show-filter-btn',
    $this->Html->link(__('Filter') . " {$icon}", '#', [
        'id' => 'show-filter',
        'class' => 'btn btn-default' . $activeFilterClass,
        'escape' => false
    ])
);
