<?php
foreach($sortSearchFields as $_column => $_searchFields): ?>
	<div class="filter-block uk-grid">
		<?php
		$sortedFilterBlock = Hash::sort($_searchFields, '{s}.weight', 'ASC');
		foreach($sortedFilterBlock as $field => $fieldOptions):
			//echo $this->Element('admin/filter_item', array('field' => $field, 'fieldOptions' => $fieldOptions));
            echo $this->SearchView->renderField($field, $fieldOptions);
		endforeach
		?>
	</div>
<?php endforeach; ?>
