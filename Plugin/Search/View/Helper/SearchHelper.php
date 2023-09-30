<?php

/**
 * Created by IntelliJ IDEA.
 * User: Sash
 * Date: 07.12.2016
 * Time: 10:14
 */
class SearchHelper extends AppHelper
{

    public function processSearchFieldOptions($field, $properties)
    {
        $matches = [];
        if (!empty($this->_View->params->query[$field]))
        {
            $properties['value'] = $this->_View->params->query[$field];
        }
		$_type = Hash::get($properties,'type', null);
        if (in_array($_type, ['value', 'like']) && preg_match('/(.*)_id$/', $field, $matches))
        {
            // add source
            $model = Inflector::camelize($matches[1]);
            if(empty($properties['data-source'])){
                $properties['data-source'] = Router::url(['admin' => true, 'plugin' => 'dictionaries', 'controller' => 'dictionaries', 'action' => 'foreign_data', 'Model' => $model, '?' => ['query' => '']]);
            }
            if (!empty($properties['value']))
            {
                $properties['data-value'] = $properties['value'];
            }
            $properties['type'] = 'select';
            $properties['empty'] = false;
        }
        else
		{
			//if(! $_type && empty($properties['options']) && empty($properties['data-options'])){
                //$properties['type'] = 'text';
            //}
		}
        return $properties;
    }

    protected function getUser(){
        if($this->user === null){
            $this->user = AuthComponent::user();
        }
        return $this->user;
    }

    public function hasUserRole($roles = null){
        if(! $roles){
            return false;
        }
        if(! $this->getUser()){
            return false;
        }
        $role = Hash::get($this->user, 'Role.alias', null);
        if($role){
            if(! is_array($roles)) $roles = [$roles];
            return in_array($role, $roles);
        }
        return false;
    }

}
