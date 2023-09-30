<?php
App::uses('BaseSyncDriver',  'Sync.Lib/Driver');

/***
 * @property SyncConnection SyncConnection
 */
class SyncShell extends AppShell
{
    public $uses = ['Sync.SyncConnection'];

    public function sync(){
        foreach ($this->SyncConnection->find('all') as $conn){
            foreach (['src', 'dst'] as $t){
                $driver = BaseSyncDriver::getWithDriverAndConfig(
                    $conn['SyncConnection'][$t.'_driver'],
                    $conn['SyncConnection'][$t.'_config']);
                $driver->sync($conn, $t);
            }
        }
    }
}