<?php
App::uses('BaseSyncDriver', 'Sync.Lib/Driver');
App::uses('SyncLog', 'Sync.Lib');
/***
 * @property SyncConnection SyncConnection
 */
class SyncConnectionsController extends AppController
{
    public function index(){
        $connections = $this->SyncConnection->find('all');
        $this->set(compact('connections'));
    }

    public function add()
    {

    }

    public function edit($id = null){
        if (!empty($this->data)){
            if ($this->SyncConnection->save($this->data)){
                $this->redirect([$this->SyncConnection->id]);
            } else {
                die('dd');
            }
        } elseif  ($id){
            $conn = $this->SyncConnection->findById($id);
            $this->request->data = $conn;
            foreach (['src', 'dst'] as $p){
                if (!empty($conn['SyncConnection'][$p.'_driver'])){
                    $this->request->data[$p]['Config'] = json_decode($conn['SyncConnection'][$p.'_config']);
                    $this->set($p.'_fields', BaseSyncDriver::getWithDriverAndConfig(
                        $conn['SyncConnection'][$p.'_driver'], $conn['SyncConnection'][$p.'_config'])->getConfigFields());
                    $this->request->data[$p]['Config'] = json_decode($conn['SyncConnection'][$p.'_config'], true);
                }
            }
        }
        $drivers = [
            'Sync.Lib/Driver/MysqlSyncDriver' => 'MySQL',
            'Sync.Lib/Driver/GoogleSpreadsheetSyncDriver' => 'Google Spreadsheet',
        ];

        $this->set(compact('drivers'));
    }
}