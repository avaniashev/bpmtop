<?php

class SyncConnection extends AppModel
{
    public function beforeSave($options = array())
    {
        foreach (['src', 'dst'] as $p){
            if (isset($this->data[$p]['Config'])){
                $this->data['SyncConnection'][$p.'_config'] = json_encode($this->data[$p]['Config']);
            }
        }

        return parent::beforeSave($options);
    }

    public function afterSave($created, $options = array())
    {
        foreach (['src', 'dst'] as $p){
            if (!empty($this->data['SyncConnection'][$p.'_driver']) && !empty($this->data['SyncConnection'][$p.'_config'])){
                $driver = BaseSyncDriver::getWithDriverAndConfig(
                    $this->data['SyncConnection'][$p.'_driver'],
                    $this->data['SyncConnection'][$p.'_config']
                );
                $driver->setupListener($this->id, $p);
            }
        }

        parent::afterSave($created, $options);
    }
}