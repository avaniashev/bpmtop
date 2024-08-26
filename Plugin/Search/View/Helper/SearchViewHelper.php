<?php
App::uses('AppHelper', 'View/Helper');
/**
 * Class SearchViewHelper
 *
 * @property View $_View
 * @property FormHelper $Form
 * @property SearchHelper $Search
 * @property SearchViewTplHelper $SearchViewTpl
 * @property HtmlHelper $Html
 */
class SearchViewHelper extends AppHelper {

	protected $_alias_field = null;
	protected $_fieldOptions = [];
	protected $_field = null;
    protected $modelName = null;

	protected $defaultOptions = [
		'empty' => true, 'required' => false, 'hiddenField' => false
	];

	protected $_divClass = 'input';

	protected $_styledSelectClass = 'chosen_search_fields';
	protected $_rangeSlider = false;
	protected $_rangeSliderDivClass = 'params-slider';

	protected $_tpl = [
		'range' => '<div class=":div-class:">
                    :label:
                    <div class="range-wrapper">
                        :label-from:
                        :input-from:
                        :label-to:
                        :input-to:
                    </div>
                    </div>',
		'range_slider' => '',
	];

	public $helpers = [
		'Form',
		'Search.Search',
		'Search.SearchViewTpl',
		'Html'
	];

	public function __construct(View $View, $settings = array()) {
		if(CakePlugin::loaded('Locations')){
			$this->helpers[] = 'Locations.Location'; //@todo clear
		}
		parent::__construct($View, $settings);
		$this->SearchViewTpl->setTpls($this->_tpl);
	}

    public function setModelName($modeName = null){
        $this->modelName = $modeName;
    }

    public function getModelName(){
	    if(empty($this->modelName)) {
	        $modelClass = $this->_View->get('modelClass');
	        $this->modelName = $modelClass;
        }

        return $this->modelName;
    }

    public function setFormDefaultOptions($options){
		$this->Form->inputDefaults($options);
	}

	public function setFormOptions(FormHelper $form, $options){
		$this->Form = $form;
		$this->Form->inputDefaults($options);
	}

	public function renderField($field = [], $fieldOptions = []){
        if (! empty($fieldOptions['label'])) {
            $fieldOptions['label'] = __($fieldOptions['label']);
        }
        if (! empty($fieldOptions['data-placeholder'])) {
            $fieldOptions['data-placeholder'] = __($fieldOptions['data-placeholder']);
        }
		if(! empty($fieldOptions['helper'])){
			$helperPlugin = null;
			if(! empty($fieldOptions['helperPlugin'])){
				$helperPlugin = $fieldOptions['helperPlugin'];
				unset($fieldOptions['helperPlugin']);
			}
			$helper = $this->getRenderHelper($fieldOptions['helper'], $helperPlugin);
			if($helper instanceof SearchViewHelper){
				return $helper->render($field, $fieldOptions);
			}
		}
        if ($fieldOptions['type'] && $fieldOptions['type'] == 'value'){
            unset($fieldOptions['type']);
        }
		return $this->render($field, $fieldOptions);
	}

	protected function getRenderHelper($helperName = null, $helperPlugin = null){
		if($helperName){
			if(! $helperPlugin){
				list($helperPlugin, $name) = pluginSplit($helperName);
			}else{
				$name = $helperName;
			}
			if (!isset($this->_View->$name)) {
				$this->_View->$name = $this->_View->Helpers->load($helperPlugin . '.' . $helperName);
			}
			return $this->_View->$name;
		}
		return false;
	}

	protected function getRangeTplData($field = null, $options = []){
		$out = [
			'div-class' => '',
			'label' => '',
			'label-from' => '',
			'input-from' => '',
			'label-to' => '',
			'input-to' => '',
		];
		if(! $this->_alias_field){
			$this->initFieldOptions($field, $options);
			$field = $this->_field;
		}
		$alias_field = $this->_alias_field;
		$out['field-alias'] = $alias_field;
		$out['slider'] = '';
		if (isset($options['slider']) || $this->_rangeSlider) {
			$out['slider'] = $this->renderSlider($alias_field, $options);
			unset($options['slider']);
		}
		$out['div-class'] = $this->getRangeDivClass($field, $options);
		$out['label'] = $this->Form->label($field.'From', $options['label']);
		if(empty($options['label_placeholder'])){
			$out['label-from'] = $this->getLabelFromTo($field, $options, 'from');
		}else{
			$out['label-from'] = '';
		}
		$out['input-from'] = $this->getInputFromTo($field, $options, 'from');
		if(empty($options['label_placeholder'])){
			$out['label-to'] = $this->getLabelFromTo($field, $options, 'to');
		}else{
			$out['label-to'] = '';
		}
		$out['input-to'] = $this->getInputFromTo($field, $options, 'to');

		return $out;
	}

	public function renderRange($field = null, $options = []){
		$out = $this->getRangeTplData($field, $options);
		$html = '<div class="'.$out['div-class'].'">
                    '.__($out['label']).'
                    <div class="range-wrapper">
                        '.$out['label-from'].'
                        '.$out['input-from'].'
                        '.$out['label-to'].'
                        '.$out['input-to'].'
                    </div>
                    </div>';
		return $html;
	}

	protected function getRangeDivClass($field = null, $options = []){
		return 'input range';
	}

	protected function getLabelFromTo($field = null, $options = [], $direction = 'from'){
		$text = $direction == 'from' ? __('от') :  __('до');
		return $this->Form->label($field.'_'.$direction, $text,['class' => 'from-to']);
	}

	protected function getInputFromTo($field = null, $options = [], $direction = 'from'){
		$options['div'] = null;
		$options['label'] = false;
		$options['class'] = ! empty($options['class']) ? $options['class'] : '';
		$options['class'] = 'filter-'.$direction . ' ' . $options['class'];
		/* if(! empty($options['type']) && $options['type'] == 'date'){
			 $text = $direction == 'from' ? 'от' : 'до';
			 $options['placeholder'] = $text;
		 }*/
		$options['type'] = 'text';
		if(!empty($options['label_placeholder'])){
			$text = $direction == 'from' ?  __('от') :  __('до');
			$options['placeholder'] = $text;
			unset($options['label_placeholder']);
		}

		$baseField =  strstr($field, '.');
		$baseField = str_replace('.', '', $baseField);

		$options['data-alias'] = $baseField . '_' . $direction;

		if(!empty($options['classes'][$direction])){
			$options['class'] .= ' '.$options['classes'][$direction];
		}
		if(! empty($options['data-toggle']) && $options['data-toggle'] == 'datetime'){
			$options['div'] = ['class' => 'input input-prepend date '.$direction.'-date'];
		}
		unset($options['classes']);
		$options = $this->clearRenderInput($options);
		return $this->Form->input($field.'_'.$direction, $options);
	}


	protected function initFieldOptions($field = null, $fieldOptions = [], $defaultOptions = []){
		//$options = ['empty' => true, 'required' => false, 'hiddenField' => false];//todo
		if (is_numeric($field) && is_string($fieldOptions)) {
			$field = $fieldOptions;
			$fieldOptions = array();
		}
		if(! empty($fieldOptions['field_name'])){
			$field = $fieldOptions['field_name'];
			unset($fieldOptions['field_name']);
		}
		$options = $defaultOptions;
		if (!empty($fieldOptions)) {
			$options = Hash::merge($options, $fieldOptions);
		}
		$alias_field = explode('.', $field);
		$alias_field = array_pop($alias_field);
		$this->_alias_field = $alias_field;
		$this->_fieldOptions = $options;
		$this->_field = $field;
	}


	/**
	 * @param null $field
	 * @param array $fieldOptions
	 */
	public function render($field = null, $fieldOptions = []){
		$defaultOptions = $this->defaultOptions;
		$this->initFieldOptions($field, $fieldOptions, $defaultOptions);
		$field = $this->_field;
		$options = $this->_fieldOptions;
		$alias_field = $this->_alias_field;
		$this->Form->unlockField($field);

		$showField = $this->allowShow($options);
		if(! empty($options['data-location'])){ //@todo Refactor move LocationSearchHelper render
			$_alias = Inflector::camelize($alias_field);
			$_alias = str_replace('Id','',$_alias);
			if($_alias == 'MethodMovement'){
				$_alias = 'TimeToMetro';
			}
			$showField = $this->Location->hasShowField($_alias);
			if(! empty($defaultLocation[$_alias])){
				$options['default'] = $defaultLocation[$_alias];
			}
			unset($options['data-location']);
		}
		if(! empty($options['hide-roles']) && $showField){
			$options['hide-roles'][] = 'admin';
			$showField = ! $this->Search->hasUserRole($options['hide-roles']);
			unset($options['hide-roles']);
		}
		if(! empty($options['allow-roles']) && $showField){
			$options['allow-roles'][] = 'admin';
			$showField = $this->Search->hasUserRole($options['allow-roles']);
			unset($options['allow-roles']);
		}

		if($showField){
			$options['data-alias'] = $alias_field;

			if (isset($options['type']) && $options['type'] == 'date') {
				return $this->renderDate($field, $options);
			} else {
				if (isset($options['type']) && $options['type'] == 'range_value') {
					return $this->renderRange($field, $options);
				} else {
					$options['hiddenFields'] = false;
					if (!empty($options['data-options'])) {
						$options['options'] = $this->getDataOptions($options);
					}
					$options = $this->Search->processSearchFieldOptions($field, $options);
					return $this->renderInput($field, $options);
				}
			}
		}
	}

	protected function getDataOptions(array $options = []){
		return  $this->_View->get($options['data-options']);
	}

	protected function allowShow(array $options = []){
		return  true;
	}

	public function renderDate($field, $options){
		$out = "";
		if (!empty($options['label'])) {
			$out .= $this->Form->label($field, $options['label']);
		}
		$out .= $this->Form->date($field, $options);

		return $out;
	}

	protected function clearRenderInput($options){
		$clearTag = [
			'weight' => 1,
			'dictionary' => 1,
			'categories' => 1,
			'alias' => 1,
			'param-type' => 1,
			'column' => 1,
			'hidden_single' => 1,
			'helper' => 1
		];

		if(!empty($options['type'])){
			if ($options['type'] == 'text' || $options['type'] == 'submit') {
				$clearTag['empty'] = 1;
			}
		}
		$options = array_diff_key($options, $clearTag);
		/*if(! isset($options['options'])){
			unset($options['empty']);
		}*/
		return $options;
	}

	public function renderInput($field, $options){
		$options = $this->clearRenderInput($options);
		//wrapper style
		/*$options['before'] = '<div class="wrapper-input">';
		$options['after'] = '</div>';*/
		return $this->Form->input($field, $options);
	}

	public function getRange(array $searchField = []){
		$range = [
			'min' => null,
			'max' => null,
			'step' => 1
		];
		if(! empty($searchField['alias'])){
			$_alias = Inflector::camelize($searchField['alias']);
			$keyLimit = 'filter'.$_alias.'Limits';
			$_range = $this->_View->get($keyLimit);
			if ($_range) {
				$range['min'] = intval(Hash::get($_range, 'min', 0));
				$range['max'] = intval(Hash::get($_range, 'max', 50));
				$range['step'] = intval(Hash::get($_range, 'step', 1));
			}
		}
		return $range;
	}

	public function renderSlider($alias_field = null, $options = []){
		$options['alias'] = $alias_field;
		$range = $this->getRange($options);
		if ($range) {
			$options['data-min'] = intval(Hash::get($range, 'min', 0));
			$options['data-max'] = intval(Hash::get($range, 'max', 50));
			$options['data-step'] = intval(Hash::get($range, 'step', 1));
			$options['data-min'] = floor($options['data-min'] / $options['data-step']) * $options['data-step'];
			$options['data-max'] = ceil($options['data-max'] / $options['data-step']) * $options['data-step'];
		}
		$data = [
			"data-field" => $alias_field,
			"data-min" => $options['data-min'],
			"data-max" => $options['data-max'],
			"data-step" => $options['data-step'],
			"data-from" => "",
			"data-to" =>   "",
		];
		return $this->Html->div($this->_rangeSliderDivClass,'', $data);
	}

	/* Next code to smaller form */

	public function renderSmallerForm()
	{
		$out = '';
		$query = $this->request->query;
		$ignoreFields = ['sort', 'direction'];

		$modelName = $this->_View->get('modelName');
		$searchFields = $this->_View->get('searchFields');
		$userFindObjectRestrictions = $this->_View->get('userFindObjectRestrictions', []);

		if ((!empty($query) || !empty($userFindObjectRestrictions)) && !empty($searchFields)) {
			foreach ($userFindObjectRestrictions as $alias => $value) {
				if(!in_array($alias, $ignoreFields)) {
					$out .= $this->renderAppropriateSmallerBlock($modelName, $searchFields, $alias, $value, false);
				}
			}
			foreach ($query as $alias => $value) {
				if(!in_array($alias, $ignoreFields)) {
					$out .= $this->renderAppropriateSmallerBlock($modelName, $searchFields, $alias, $value);
				}
			}

			if (!empty($query)) {
				$out .= $this->Html->link(__('Сбросить'), '#', ['id' => 'SmallerFormClear', 'class' => 'small-filter-info JS-clean-filter']);
			}
			$out = $this->Html->div('span11 small-filter-block', $out);
		}

		return $out;
	}

	/**
	 * Получить smaller block для поля поиска в контексте некоторой модели.
	 *
	 * @param string $modelAlias В контексте этой модели происходит рендер
	 * @param array $availableSearchFields
	 * @param string $searchFieldAlias Алиас поля поиска, для которого осуществить рендер
	 * @param string $searchFieldValue Значение поля поиска, для которого осуществить рендер
	 */
	private function renderAppropriateSmallerBlock(
		$modelAlias, $availableSearchFields, $searchFieldAlias, $searchFieldValue, $ifRemovableBlock = true
	) {
		$out = '';
		if (!empty($availableSearchFields[$searchFieldAlias])) {
			return $this->renderSmallerBlock(
				$availableSearchFields[$searchFieldAlias], $searchFieldAlias, $searchFieldValue, $ifRemovableBlock
			);
		}
		else if(!empty($availableSearchFields[$modelAlias . '.' . $searchFieldAlias])) {
			return $this->renderSmallerBlock(
				$availableSearchFields[$modelAlias . '.' . $searchFieldAlias],
				$searchFieldAlias, $searchFieldValue, $ifRemovableBlock
			);
		}
		else if(strpos($searchFieldAlias,'_from') !== false) {
			return $this->renderSmallerBlockRange(
				$availableSearchFields, $searchFieldAlias, $searchFieldValue, 'from', $ifRemovableBlock
			);
		}
		else if(strpos($searchFieldAlias,'_to') !== false) {
			return $this->renderSmallerBlockRange(
				$availableSearchFields, $searchFieldAlias, $searchFieldValue, 'to', $ifRemovableBlock
			);
		}
		else {
			return $out;
		}
	}

	protected function renderSmallerBlockRange($searchFields = [], $alias = '', $value = null, $direction, $ifRemovableBlock = true)
	{
        $out = '';
		$modelName = $this->_View->get('modelName');
		$_alias = str_replace('_' . $direction, '', $alias);

		if (!empty($searchFields[$_alias])) {
			$block = $searchFields[$_alias]['label'] . ' ' . __($direction) . ': ' . $value;
		}
		else if(!empty($searchFields[$modelName . '.' . $_alias]['label'])) {
			$block = $searchFields[$modelName . '.' . $_alias]['label'] . ' ' . __($direction) . ': ' . $value;
		}
		else {
			$block = __($alias) . ' ' . __($direction) . ': ' . $value;
		}

		if (!empty($block)) {
			$smallFilterInfoClassName = 'small-filter-info';
			$smallFilterInfoContent = $block;
			if ($ifRemovableBlock) {
				$iconRemove = $this->Html->tag('i', '', ['class' => 'icon-remove']);
				$removeBtn = $this->Html->link($iconRemove, '#', ['data-alias' => $alias, 'data-model' => $modelName, 'class' => 'clear-item']);
				$smallFilterInfoContent .= $removeBtn;
			} else {
				$smallFilterInfoClassName .= ' not-removable';
			}
			$out .= $this->Html->div($smallFilterInfoClassName, $smallFilterInfoContent);
		}

		return $out;
	}

	protected function renderSmallerBlock($searchFieldData = [], $alias = null,  $value = [], $ifRemovableBlock = true)
	{
		$out = '';
		$modelName = $this->_View->get('modelName');
		$processValue = $this->getSmallBlockValue($searchFieldData, $alias, $value);
		if(!empty($processValue)) {
			foreach($processValue as $valueItem) {
				$smallFilterInfoClassName = 'small-filter-info';
				$smallFilterInfoContent = $valueItem;
				if ($ifRemovableBlock) {
					$iconRemove = $this->Html->tag('i', '', ['class' => 'icon-remove']);
					$removeBtn = $this->Html->link($iconRemove, '#', ['data-alias' => $alias, 'data-model' => $modelName, 'class' => 'clear-item']);
					$smallFilterInfoContent .= $removeBtn;
				} else {
					$smallFilterInfoClassName .= ' not-removable';
				}
				$out .= $this->Html->div($smallFilterInfoClassName, $smallFilterInfoContent, ['data-alias' => $alias, 'title' => $alias,]);
			}
		}

		return $out;
	}

	protected function getSmallBlockValue($searchFieldData = [], $alias = null, $value = null)
	{
        $processValue = [];
		if(!is_array($value)) {
			$value = [$value];
		}

		if(!empty($searchFieldData['type']) && $searchFieldData['type'] == 'hidden') {
			return '';
		}

		if (!empty($searchFieldData['options'])) {
			foreach ($value as $item) {
				if(!empty($searchFieldData['options'][$item])) {
					$processValue[] = $searchFieldData['options'][$item];
				}
			}
		}

		if(!empty($searchFieldData['data-options']) && empty($processValue)) {
			$valuesOptions = $this->_View->get($searchFieldData['data-options']);
			foreach ($value as $item) {
				if(!empty($valuesOptions[$item])) {
					$processValue[] = $valuesOptions[$item];
				}
			}
		}

		if (!empty($searchFieldData['helper'])) {
			$smallSearchHelper = $searchFieldData['helper'];

			if (method_exists($this->_View->$smallSearchHelper, 'renderSmallFilterBlock')) {
				$processValue = $this->_View->$smallSearchHelper->renderSmallFilterBlock($alias, $value);
			}
		}

		if(empty($processValue)) {
			foreach($value as $itemValue) {
				if(isset($searchFieldData['type']) && $searchFieldData['type'] == 'checkbox') {
				    if(! empty($searchFieldData['label'])){
                        $processValue = [$searchFieldData['label']];
                    }else{
                        $processValue = [__($alias)];
                    }
				} else {
					$processValue = [__($alias) . ': ' . $itemValue];
				}
			}
		}

		return $processValue;
	}
}
