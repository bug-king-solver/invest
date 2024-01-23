<?php

namespace App\Misc;

use Exception;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logs
{
    /**
     * @param $file
     * @param $type
     * @return Logger
     */
    public static function build($file, $type)
    {
        return new Logger($type, [
            new StreamHandler(storage_path('logs/' . $file . '.log'), Logger::INFO)
        ]);
    }

    /**
     * @param $file
     * @param $type
     * @return Logger
     */
    public static function daily($file, $type = 'info')
    {
        return new Logger($type, [
            new StreamHandler(storage_path('logs/' . $file . '-' . Carbon::today()->toDateString() . '.log'), Logger::INFO)
        ]);
    }

    /**
     * @param $file
     * @param \Throwable $exception
     */
    public static function logToDaily($file, \Throwable $exception)
    {
        $log = static::daily($file);
        $log->info(join(' | ', [$exception->getFile(), $exception->getLine(), $exception->getMessage()]));
    }

    public static function logMessageToDaily($file, $message)
    {
        $log = static::daily($file);
        $log->info($message);
    }
}
