<?php
echo $this->Html->div('container',
    $this->Html->link('add', ['action' => 'edit']));
$connectionsHtml = '';
foreach ($connections as $connection){
    $connectionsHtml .= $this->Html->div('row', $this->Html->div('col',
        $this->Html->link($connection['SyncConnection']['name'],
            ['controller' => 'sync_connection_fields', $connection['SyncConnection']['id']])
        .' '.$this->Html->link('edit', ['action' => 'edit', $connection['SyncConnection']['id']])
    ));
}
echo $this->Html->div('container', $this->Html->div('row', $connectionsHtml));
//debug($connections);

$logContent = '';
foreach (array_reverse(SyncLog::getLog()) as $log){
    $logContent .= $this->Html->div('row',
        $this->Html->div('col-2', date("m/d/Y H:i:s", $log['time']))
        .$this->Html->div('col-3', $log['message'])
        .$this->Html->div('col-7', $log['trace'])
    );
}
echo $this->Html->div('container', $logContent);
//debug(SyncLog::getLog());