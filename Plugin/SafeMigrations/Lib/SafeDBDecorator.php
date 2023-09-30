<?php

class SafeDBDecorator {

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function __call($name, $arguments)
    {
        try {
            return call_user_func_array([$this->db, $name], $arguments);
        }
        catch (Exception $ex)
        {
            if (strpos($ex->getMessage(), "Duplicate column name") !== false ) return true;
            if (strpos($ex->getMessage(), "Base table or view already exists") !== false ) return true;
            if (strpos($ex->getMessage(), "Duplicate key name") !== false ) return true;
            if (strpos($ex->getMessage(), "already exists in") !== false ) return true;
            if (strpos($ex->getMessage(), "check that column/key exists") !== false ) return true;

            //debug($ex);
            throw $ex;
        }
    }

} 