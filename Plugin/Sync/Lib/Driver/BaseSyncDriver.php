<?php
App::uses('SyncLog', 'Sync.Lib');
class BaseSyncDriver
{
    public $config = [];
    public $remoteDriver = null;

    /***
     * @param $driver
     * @return BaseSyncDriver
     */
    public static function get($driver){
        $pos = strrpos($driver, '/');
        $class = substr($driver, $pos + 1);
        App::uses($class, substr($driver, 0, $pos));
        return new $class();
    }

    public static function getWithDriverAndConfig($driver, $config){
        $obj = static::get($driver);
        $obj->configure(json_decode($config, true));
        return $obj;
    }

    public function getConfigFields(){
        return [];
    }

    public function configure($config){
        $this->config = $config;
    }

    public function getRemoteFields($type = 'list'){
    }

    public function setupListener($connectionId, $type){
    }

    public function sync($connectionId, $type){

    }

    public function receiveData($data, $idField){

    }

    /**
     * @param $connectionId
     * @param $type
     * @param string $remoteType
     * @param array $u
     * @param BaseSyncDriver $remoteDriver
     * @return void
     */
    public function sendUpdateToRemote($connection, $type, array $u)
    {
        $idField = null;
        $remoteType = $type == 'src' ? 'dst' : 'src';
        if (empty($this->remoteDriver)){
            $this->remoteDriver = BaseSyncDriver::getWithDriverAndConfig(
                $connection['SyncConnection'][$remoteType.'_driver'], $connection['SyncConnection'][$remoteType.'_config']);
        }
        $fields = ClassRegistry::init('Sync.SyncConnectionField')->find('all', [
            'conditions' => ['sync_connection_id' => $connection['SyncConnection']['id']]]);
        $data = [];
        foreach ($fields as $field) {
            $localField = $field['SyncConnectionField'][$type . '_field'];
            if (empty($u[$localField])) continue;
            $remoteField = $field['SyncConnectionField'][$remoteType . '_field'];
            $data[$remoteField] = $u[$localField];
            if ($this->isIdFieldLocal($localField)) {
                $idField = $remoteField;
            }
        }
        $res = null;
        try {
            $res = $this->remoteDriver->receiveData($data, $idField);
        } catch (Exception $e){
            SyncLog::log($e->getMessage());
        }

        return $res;
    }

    /**
     * @param mixed $localField
     * @return bool
     */
    public function isIdFieldLocal($localField): bool
    {
        return false;
    }
}