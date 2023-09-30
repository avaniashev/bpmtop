<?php
/**
 * Created by IntelliJ IDEA.
 * User: macbookair
 * Date: 05.01.17
 * Time: 13:15
 */

class SearchComponent extends Component
{
	public $components = ['Search.Prg'];
	protected $controller;

	public function startup(Controller $controller)
	{
		$this->controller = $controller;
	}

	public function processSearch($model, $conditions = [])
	{
		if ($model->Behaviors->loaded('Searchable'))
		{
			$this->controller->set('searchFields', $model->filterArgs);
			$this->Prg->init($this->controller);
			$this->Prg->commonProcess($model->name);
			$conditions = array_merge($model->parseCriteria($this->controller->request->query), $conditions);
		}
		return $conditions;
	}

}
