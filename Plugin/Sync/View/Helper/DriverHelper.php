<?php

/***
 * @property HtmlHelper Html
 * @property FormHelper Form
 */
class DriverHelper extends Helper
{
    public $helpers = ['Form', 'Html'];
    public function form($prefix){
        $form = $this->Html->tag('h3', 'Form '.$prefix);
        if (empty($this->data['SyncConnection'][$prefix.'_driver'])){
            $form .= $this->Form->input($prefix.'_driver', ['options' => $this->_View->viewVars['drivers']]);
        } else {
            $form .= $this->Form->input($prefix.'_driver', ['type' => 'hidden']);
            foreach ($this->_View->viewVars[$prefix.'_fields'] as $key => $options){
                $form .= $this->Html->div('row', $this->Form->input($prefix.'.'.$key, array_merge([
                    'div' => false,
                    'before' => '<div class="col">',
                    'between' => '</div><div class="col">',
                    'after' => '</div>',
                    'legend' => false], $options)));
            }
        }
        return $form;
    }
}