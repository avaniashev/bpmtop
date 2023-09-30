<?php

class SyncWebhookController extends AppController
{
    public function process($connectionId, $type){
        CakeLog::debug(print_r($_POST, true));
        CakeLog::debug(print_r($_GET, true));
        CakeLog::debug(file_get_contents('php://input'));
        die();
    }
}