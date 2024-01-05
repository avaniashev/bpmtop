<?php

class BootstrapHelper extends AppHelper
{
    public $helpers = ['Html', 'Form'];

    public function floatingInput($field, $options){
        $defaults = ['label' => '', 'type' => 'text'];
        $defaults = array_merge($defaults, $options);
        return $this->Html->div('form-floating mb-3',
            $this->Form->input($field, [
                'label' => false, 'div' => false, 'placeholder' => $defaults['label'], 'class' => 'form-control',
                'type' => $defaults['type']]).
            $this->Form->label($field, $defaults['label']));
    }
}