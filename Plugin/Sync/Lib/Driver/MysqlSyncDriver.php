<?php
App::uses('SyncLog', 'Sync.Lib');
class MysqlSyncDriver extends BaseSyncDriver
{
    public $connection;

    public function getConfigFields(){
        $databases = [];
        if  (!empty($this->config['host'])){
            $res = $this->query('show databases');
            while ($t = mysqli_fetch_assoc($res)){
                $databases[$t['Database']] = $t['Database'];
            }
        }
        $tables = [];
        if  (!empty($this->config['database'])){
            $res = $this->query("show tables from {$this->config['database']}");
            while ($t = mysqli_fetch_assoc($res)){
                $table = current($t);
                $tables[$table] = $table;
            }
        }
        return [
            'Config.host' => ['type' => 'text'],
            'Config.port' => ['type' => 'text', 'default' => 3306],
            'Config.user' => ['type' => 'text'],
            'Config.password' => ['type' => 'text'],
            'Config.database' => ['type' => 'select', 'options' => $databases, 'empty' => true],
            'Config.table' => ['type' => 'select', 'options' => $tables, 'empty' => true],
        ];
    }

    public function configure($config){
        parent::configure($config);
        if (empty($config)){
            return;
        }
        $this->connection = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        if (!$this->connection){
            debug(mysqli_error($this->connection));
        }
    }

    public function query($query){
        $res = mysqli_query($this->connection, $query);
        $error = mysqli_error($this->connection);
        if (!empty($error)){
            SyncLog::log($error.' SQL: '.$query);
        }
        return $res;
    }

    public $remoteFields = [];
    public function getRemoteFields($type = 'list'){
        if (empty($this->remoteFields)){
            $res = mysqli_query($this->connection, "show columns from {$this->config['table']}");
            if (!$res){
                debug(mysqli_error($this->connection));
            }
            while ($c = mysqli_fetch_assoc($res)){
                $this->remoteFields[$c['Field']] = $c;
            }
        }
        if ($type == 'list'){
            $result = [];
            foreach ($this->remoteFields as $c){
                $result[$c['Field']] = $c['Field'] . ' - ' . $c['Type'];
            }
            return $result;
        } else {
            return $this->remoteFields;
        }

    }

    public function setupListener($connectionId, $type){
        $this->query("create table if not exists sync_updates (action varchar(1), connector int, type varchar(3), row_id int)");
        $actions = ['insert', 'update',];
        foreach ($actions as $action){
            $triggerName = 'sync_connection_'.$connectionId.'_'.$type.'_'.$action;
            $this->query("drop trigger if exists $triggerName");

            $trigger = "create trigger $triggerName after $action on {$this->config['database']}.{$this->config['table']}
                FOR EACH ROW insert into sync_updates (`action`, `connector`, `type`, `row_id`) values ('{$action[0]}', $connectionId, '$type', NEW.id)";
            $this->query($trigger);
        }
    }

    public function sync($connection, $type){
        return ;
        $connectionId = $connection['SyncConnection']['id'];

        $updates = $this->query("select data.* from sync_updates u
            join {$this->config['table']} as data on data.id = u.row_id
            where connector = $connectionId and type = '$type'");
        while ($u = mysqli_fetch_assoc($updates)){
            $this->sendUpdateToRemote($connection, $type, $u);
        }
        $this->query("delete from sync_updates where connector = $connectionId and type = '$type'");
    }

    /**
     * @param mixed $localField
     * @return bool
     */
    public function isIdFieldLocal($localField): bool
    {
        return $localField == 'id';
    }

    public function receiveData($data, $idField)
    {
        return;
        $id = !empty($data['id']) ? $data['id'] : (!empty($data['ID']) ? $data['ID'] : null);
        if (!empty($id)){
            $res = $this->query("select * from {$this->config['table']} where id = {$id}");
            if (mysqli_num_rows($res) > 0){
                $current = mysqli_fetch_assoc($res);
                $diff = array_diff($data, $current);
                if (!empty($diff)){
                    $updates = [];
                    foreach ($diff as $k => $v){
                        $updates[] = "`$k` = '".$this->sanitize($k, $v)."'";
                    }
                    if ($this->query("update {$this->config['table']} set ".join(',', $updates)." where id = {$id}")){
                        // delete update that was mande after our operation
                        $this->query("delete from sync_updates where row_id = {$id}");
                    };
                }
            }
            return null;
        }
        // Otherwise insert
        $fields = [];
        $values = [];
        foreach ($data as $key => $value){
            if (!empty($value)){
                $fields[] = "`$key`";
                $values[] = "'".$this->sanitize($key, $value)."'";
            }

        }
        $query = "insert into {$this->config['table']} (" . join(',', $fields) . ") 
                values (" . join(',', $values) . ")";
        $res = $this->query($query);
        $error = mysqli_error($this->connection);
        if ($error){
            SyncLog::log($error.' SQL: '.$query);
        }
        $id = mysqli_insert_id($this->connection);
        return ['idField' => 'id', 'idValue' => $id];
    }

    public function sanitize($field, $data){
        $remoteFields = $this->getRemoteFields('object');

        if (strpos($remoteFields[$field]['Type'], 'datetime') === 0){
            $data = date('Y-m-d H:i:s', strtotime($data));
        }
        if (strpos($remoteFields[$field]['Type'], 'date') === 0){
            $data = date('Y-m-d', strtotime($data));
        }
        if (strpos($remoteFields[$field]['Type'], 'int') === 0
            || strpos($remoteFields[$field]['Type'], 'tinyint') === 0){
            $data = intval($data);
        }
        if (preg_match('/varchar\((\d+)\)/', $remoteFields[$field]['Type'], $r)){
            $data = str_replace("'", "\\'", $data);
            if (strlen($data) > $r[1]){
                $data = substr($data, 0, $r[1]);
            }
        } else if (strpos($remoteFields[$field]['Type'], 'enum') !== false){
            return null;
        }
        return $data;
    }


}