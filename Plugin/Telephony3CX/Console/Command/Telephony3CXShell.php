<?php

/***
 * @property Telephony3CXLog Telephony3CXLog
 */
class Telephony3CXShell extends AppShell
{
    public $uses = ['Telephony3CX.Telephony3CXLog'];

    public function run(){
        Configure::write('debug', 1);
        debug('Start');
        while (true){
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_connect($socket, Configure::read('Telephony3CX.ip'), Configure::read('Telephony3CX.port'));
            while ($result = socket_read($socket, 5000, PHP_NORMAL_READ)){
                debug($result);
                $this->processLine($result);
            }
            debug('Sleep and retry');
            sleep(1);
        }
    }

    public function processLine($line){
        $line = trim($line, "\r\n ");
        if (empty($line)){
            return;
        }
        $data = explode(',', $line);
//        debug($data);
        $duration = 0;
        if (!empty($data[2])){
            $durationItems = explode(':', $data[2]);
            $duration = intval($durationItems[2]) + intval($durationItems[1]) * 60 + intval($durationItems[0]) * 60 * 60;
        }
        $log = [
            'id' => $data[3].'_'.$data['1'],
            'duration_seconds' => $duration,
            'time' => date('Y-m-d H:i:s', strtotime($data[3])),
            'phone' => $data[7],
//            'ext' => $data[9],
            'ext' => $data[14],
            'status' => $data[6],
            'call_chain' => $data[19],
            'from_dn' => $data[9],
            'to_dn' => $data[10]

        ];
        debug(json_encode($log));
        $this->Telephony3CXLog->create();
        $this->Telephony3CXLog->save($log);
    }

    public function test(){
        Configure::write('debug', 1);
        $line = "Call 612504,00000C21DE5C2470_6531,00:05:36,2023/09/19 13:33:54,2023/09/19 13:33:54,2023/09/19 13:39:31,TerminatedBySrc,+353861777149,Ext.960,10021,960,960,QFwdToDNA,Ext.504,504,,,,,Chain: +353861777149'Ext.960;Ext.963;Ext.509;Ext.504;";
        $this->processLine($line);
    }

    public function log(){
        Configure::write('debug', 1);
        debug($this->Telephony3CXLog->find('all'));
    }
}