<?php
$this->extend('Search.search_markup');
$this->Helpers->load('Search.Search');
$this->Helpers->load('Search.SearchView');

if (empty($modelClass)) {
    $modelClass = Inflector::singularize($this->name);
}
if (!isset($className)) {
    $className = strtolower($this->name);
}

$useMapSearch = isset($useMapSearch) ? $useMapSearch : false;
$useSearchCreateBid = isset($useSearchCreateBid) ? $useSearchCreateBid : false;
if (!empty($searchFields)):
	$this->start('searchButtons');
    if (!empty($searchFields['custom_element'])) :
        echo $this->Element($searchFields['custom_element']);
        return;
    endif;
    if (isset($showFilterBtn) && $showFilterBtn):
        $link = $this->Html->link('Фильтр <i class="icon-filter"></i>', '#',
            array('id' => 'show-filter', 'class' => 'btn btn-default'));
        echo $this->Html->tag('div', $link, array('class' => 'show-filter-btn', 'escape' => false));
    endif;
    $this->end();
        $_searchUrl = [
            'plugin' => $this->request->params['plugin'],
            'controller' => $this->request->params['controller'],
            'action' => $this->request->params['action'],
        ];
        if(empty($searchUrl)){
            $searchUrl = $_searchUrl;
        }
        $this->start('searchForm');
        echo $this->Form->create(
            $modelClass,
            array(
                'class' => 'form-inline',
                'novalidate' => true,
                'url' => $searchUrl,
            )
        );
        echo $this->Form->input(
            'chooser',
            array(
                'type' => 'hidden',
                'value' => isset($this->request->query['chooser']),
            )
        );
        $this->end();
            $i = 0;
            if(!empty($searchFields) && is_array($searchFields)) {
                $sorted = Hash::sort($searchFields, '{s}.column', 'ASC');
                $sortSearchFields = $filterBottoms = [];
                foreach($sorted as $field => $fieldOptions){
                    $column = is_array($fieldOptions) ? Hash::get($fieldOptions, 'column', 0) : 0;
                    if(! isset($sortSearchFields[$column])){
                        $sortSearchFields[$column] = [];
                    }
                    if (!isset($fieldOptions['filterBottom'])) {
                        $sortSearchFields[$column][$field] = $fieldOptions;
                    }else{
                        $filterBottoms[$field] = $fieldOptions;
                    }
                }
                ksort($sortSearchFields);
            }
			$this->start('searchColumns');?>

    <?php
    //echo $this->Element('Search.columns', ['sortSearchFields' => $sortSearchFields]);

	$columns = 0;
    foreach($sortSearchFields as $_column => $_searchFields):
		$columns = max($columns, $_column);
		?>
            <div class="filter-block uk-grid">
                <?php
                $sortedFilterBlock = Hash::sort($_searchFields, '{s}.weight', 'asc');
                foreach($sortedFilterBlock as $field => $fieldOptions):
                    echo $this->SearchView->renderField($field, $fieldOptions);
                endforeach
                ?>
            </div>
<?php
	endforeach;

	// Теперь мы знаем, сколько колонок в фильтре и можем назначить класс

	$this->set('className', $className . ' cols' . ($columns + 1));
?>
            <?php
            $this->end();
            $this->start('searchBottom');
            foreach ($filterBottoms as $field => $fieldOptions) : ?>
                    <?php echo $this->SearchView->renderField($field, $fieldOptions); ?>
                <?php endforeach;
			$this->end();

			$this->startIfEmpty('searchActions');
            echo $this->Form->submit(
                __d('croogo', 'Apply'),
                array('div' => 'input submit', 'class' => 'btn btn-primary')
            );
            echo $this->Form->button(
                __d('croogo', 'Reset'),
                array('class' => 'btn btn-default JS-clean-filter', 'style' => 'margin:10px;float:right;')
            );
			echo $this->fetch('searchAdditionalActions');
			$this->end();


            $this->start('searchFormEnd');
            echo $this->Form->end();
            $this->end();

    ?>
<?php endif; ?>
<?php $this->start('searchSmallerForm'); ?>
	<?php $useSmallFilter = isset($useSmallFilter) ? $useSmallFilter : false; ?>
	<?php if (!empty($useSmallFilter)): ?>
		<?php echo $this->SearchView->renderSmallerForm(); ?>
		<?php echo $this->Html->script(['Search.smaller_filter.js']); ?>
	<?php endif; ?>
<?php $this->end(); ?>
