<?php
App::uses('BaseSyncDriver', 'Sync.Lib/Driver');
/***
 * @property SyncConnection SyncConnection
 * @property SyncConnectionField SyncConnectionField
 */
class SyncConnectionFieldsController extends AppController
{
    public $uses = ['Sync.SyncConnection', 'Sync.SyncConnectionField'];
    public $components = ['RequestHandler'];

    public function index($connectionId){
        if (!empty($this->data)){
            $this->SyncConnectionField->deleteAll(['sync_connection_id' => $connectionId]);
            foreach ($this->data['SyncConnection'] as $srcField => $dstField){
                if (!empty($dstField)){
                    $this->SyncConnectionField->create();
                    $this->SyncConnectionField->save([
                        'sync_connection_id' => $connectionId,
                        'src_field' => $srcField,
                        'dst_field' => $dstField,
                    ]);
                }
            }
        } else {
            $fields = $this->SyncConnectionField->find('all', ['conditions' => ['sync_connection_id' => $connectionId]]);
            foreach ($fields as $f){
                $this->request->data['SyncConnection'][$f['SyncConnectionField']['src_field']]
                    = $f['SyncConnectionField']['dst_field'];
            }
        }
        $connection = $this->SyncConnection->findById($connectionId);
        foreach (['src', 'dst'] as $p){
            $driver = BaseSyncDriver::getWithDriverAndConfig($connection['SyncConnection'][$p.'_driver'],
                $connection['SyncConnection'][$p.'_config']);
            $fields = $driver->getRemoteFields('list');
            $this->set($p.'_fields', $fields);
        }

    }


}