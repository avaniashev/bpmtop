<?php
/**
 * Created by PhpStorm.
 * User: k1785
 * Date: 26.05.2017
 * Time: 12:39
 */

class PageSpeed {

    protected static $_points = [];

    public static function all(){
        $start = self::requestStartTime();
        $now = microtime(true);
        $speed = ($now - $start);
        return  $speed;
    }

    public static function point($point = null){
        $speed = self::all();
        self::$_points[] = [
            'point' => $point,
            'speed' => $speed,
            'memory' => self::memory(),
        ];
        return $speed;
    }

    public static function points(){
        return self::$_points;
    }

    public static function statistic(){
        $statistic = [
            'items' => [],
            'all' => 0,
        ];
        $count = count(self::$_points) - 1;
        $all = self::$_points[$count]['speed'];
        $prev = 0;
        foreach (self::$_points as $point){
            $diff = $point['speed'] - $prev;
            $point['speed_ms'] = $point['speed'] * 1000;
            $point['diff'] = $diff * 1000;
            $point['percent'] = $diff/$all * 100;
            $point['progress'] = $point['speed']/$all * 100;
            $statistic['items'][] = $point;
            $prev = $point['speed'];
        }
        $statistic['all'] = $all;
        return $statistic;
    }



    public static function requestStartTime() {
        if (defined('TIME_START')) {
            $startTime = TIME_START;
        } elseif (isset($GLOBALS['TIME_START'])) {
            $startTime = $GLOBALS['TIME_START'];
        } else {
            $startTime = env('REQUEST_TIME');
        }
        return $startTime;
    }

    public static function memory(){
        $memory = memory_get_usage();
        $memory = $memory / (1024 * 1024);
        return round($memory, 3);
    }
}