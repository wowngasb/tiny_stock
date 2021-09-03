<?php

namespace app\Console;

use app\Console\Commands\Inspire;
use Exception as ExceptionAlias;

class Kernel
{

    private static $_schedule_def_map = [
        Inspire::class => ['hourly',],
    ];

    private static $_schedule_class_map = [];

    /**
     * 通过代码方式执行定时任务
     * @param int $ts 调用时的 时间戳 后端任务队列系统 保证每分钟 执行一次
     * @param bool $onlyCurrent
     * @param callable|null $log_callback
     * @return array 执行的结果
     * @throws ExceptionAlias
     */
    public static function runSchedule($ts, $onlyCurrent = true, callable $log_callback = null)
    {
        $now_min = intval(date('i', $ts));
        $count_min = intval($ts / 60);

        $hi = date('H:i', $ts);
        $day_hi = date('Y-m-d H:i', $ts);
        $ret = [];
        foreach (self::listSchedules() as $cmd => $item) {
            $cmd = explode('#', $cmd)[0];

            $s_type = $item[0];
            switch ($s_type) {
                case 'everyMinute':
                    $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    break;
                case 'hourly':
                    if ($now_min == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'daily':
                    if ($hi == '00:00') {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'dailyRange':
                    if (!empty($item[1]) && !empty($item[2]) && $hi >= $item[1] && $hi <= $item[2]) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'perMinutes':
                    $num = !empty($item[1]) && $item[1] > 1 ? intval($item[1]) : 1;
                    if ($count_min % $num == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'everyFiveMinutes':
                    if ($count_min % 5 == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'everyTenMinutes':
                    if ($count_min % 10 == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case  'datetimeAt':
                    if (!empty($item[1]) && $day_hi == $item[1]) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case  'datetimeAtRange':
                    if (!empty($item[1]) && !empty($item[2]) && $day_hi >= $item[1] && $day_hi <= $item[2]) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'dailyAt':
                    if (!empty($item[1]) && $hi == $item[1]) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'skip':
                case 'none':   // 跳过任务执行
                    break;
                default:
                    throw new ExceptionAlias("error schedule type: {$s_type}");
            }
        }
        return $ret;
    }

    /**
     * @param $cmd
     * @param $timestamp
     * @param bool $onlyCurrent
     * @param callable|null $log_callback
     * @return array
     */
    public static function runScheduleSite($cmd, $timestamp, $onlyCurrent = true, callable $log_callback = null)
    {
        $log_list = [];
        foreach (self::$_schedule_def_map as $class_str => $item) {
            $schedule_class = explode('#', $class_str)[0];
            if (empty($schedule_class)) {
                continue;
            }
            $schedule = !empty(self::$_schedule_class_map[$schedule_class]) ? self::$_schedule_class_map[$schedule_class] : new $schedule_class();
            if ($schedule instanceof SiteCommand) {
                self::$_schedule_class_map[$schedule_class] = $schedule;
            }

            /** @var SiteCommand $schedule */
            if ($schedule instanceof SiteCommand && $schedule->getName() == $cmd) {
                $schedule->setLogHandle(function ($line, $tag) use (&$log_list, $log_callback) {
                    $log_list[] = date('Y-m-d H:i:s') . " [{$tag}] {$line}";
                    if (!empty($log_callback)) {
                        call_user_func_array($log_callback, [$line, $tag]);
                    }
                });
                if ($onlyCurrent) {
                    $schedule->handleCurrentSite($timestamp);
                } else {
                    $schedule->handle($timestamp);
                }
            }
        }

        return $log_list;
    }

    public static function listCommands()
    {
        $ret = [];
        foreach (self::$_schedule_def_map as $class_str => $item) {
            $schedule_class = explode('#', $class_str)[0];
            if (empty($schedule_class)) {
                continue;
            }
            $schedule = !empty(self::$_schedule_class_map[$schedule_class]) ? self::$_schedule_class_map[$schedule_class] : new $schedule_class();
            if ($schedule instanceof SiteCommand) {
                self::$_schedule_class_map[$schedule_class] = $schedule;
            }

            /** @var SiteCommand $schedule */
            $cmd = $schedule instanceof SiteCommand ? $schedule->getName() : '';
            if (!empty($cmd)) {
                $ret[$cmd] = $schedule->getDoc();
            }
        }
        return $ret;
    }

    public static function listSchedules()
    {
        $ret = [];
        foreach (self::$_schedule_def_map as $class_str => $item) {
            $schedule_arr = explode('#', $class_str, 2);
            $schedule_class = $schedule_arr[0];
            $schedule_ext = !empty($schedule_arr[1]) ? trim($schedule_arr[1]) : '';
            if (empty($schedule_class)) {
                continue;
            }

            $schedule = !empty(self::$_schedule_class_map[$schedule_class]) ? self::$_schedule_class_map[$schedule_class] : new $schedule_class();
            if ($schedule instanceof SiteCommand) {
                self::$_schedule_class_map[$schedule_class] = $schedule;
            }

            /** @var SiteCommand $schedule */
            $cmd = $schedule instanceof SiteCommand ? $schedule->getName() : '';
            if (!empty($cmd)) {
                $cmd = !empty($schedule_ext) ? "{$cmd}#{$schedule_ext}" : $cmd;
                $ret[$cmd] = $item;
            }
        }
        return $ret;
    }

}
