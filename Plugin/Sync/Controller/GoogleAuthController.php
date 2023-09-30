<?php
App::uses('GoogleSpreadsheetSyncDriver', 'Sync.Lib/Driver');
class GoogleAuthController extends AppController
{
    public function auth(){
        $driver = new GoogleSpreadsheetSyncDriver();
        $client = $driver->createClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($accessToken);
        $driver->saveToken($client);
        die('Success');
    }
}