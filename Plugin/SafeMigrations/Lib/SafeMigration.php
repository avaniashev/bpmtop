<?php

App::uses('SafeDBDecorator', 'SafeMigrations.Lib');

class SafeMigration extends CakeMigration{

    public function __construct($options = array()) {
        $options['precheck'] = 'SafeMigrations.PrecheckSafe';
        parent::__construct($options);
    }

    protected function _run() {
        $this->db = new SafeDBDecorator($this->db);
        return parent::_run();
    }

} 