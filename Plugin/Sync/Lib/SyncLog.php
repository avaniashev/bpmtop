<?php

class SyncLog
{
    public static function log($message){
        $log = self::getLog();
        if (!$log){
            $log = [];
        }
        $log[] = [
            'time' => time(),
            'message' => $message,
            'trace' => Debugger::trace(),
        ];
        $log = array_slice($log, -10);
//        debug($log);
        file_put_contents(static::getFile(), serialize($log));
    }

    /**
     * @return string
     */
    public static function getFile()
    {
        return TMP . 'logs/sync.log';
    }

    /**
     * @return array
     */
    public static function getLog()
    {
        $file = self::getFile();
        $log = unserialize(file_get_contents($file));
        return $log;
    }
}