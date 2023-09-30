<?php
foreach ($drivers as $class => $name){
    echo $this->Html->div('', $this->Html->link($name, [
        'action' => 'edit', '?' => ['driver' => $class]]));
}