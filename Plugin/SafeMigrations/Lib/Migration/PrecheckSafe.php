<?php
App::uses('PrecheckException', 'Migrations.Lib/Migration');

class PrecheckSafe extends PrecheckException {

    public function checkDropTable($table) {
        return true;
    }

    public function checkDropField($table, $field) {
        return true;
    }

    public function checkCreateTable($table) {
        return true;
    }

    public function checkAddField($table, $field)
    {
        return true;
    }

} 