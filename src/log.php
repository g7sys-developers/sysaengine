<?php
namespace sysaengine;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class log {
    /**
     * Log info into a file
     * @param string $message
     * @return void
     */
    public static function logInfo(string $message) : void
    {
        list($logPath, $logName) = sysa::getLogData();
        $logger = new Logger($logName);
        $filename = "$logPath/$logName-".date('Y-m').'.log';
        $logger->pushHandler(new StreamHandler($filename, Logger::INFO));
        $logger->pushHandler(new FirePHPHandler());
        $logger->info($message);
    }

    /**
     * Log debug into a file
     * @param string $message
     * @return void
     */
    public static function logDebug(string $message) : void
    {
        list($logPath, $logName) = sysa::getLogData();
        $logger = new Logger($logName);
        $filename = "$logPath/$logName-".date('Y-m').'.log';
        $logger->pushHandler(new StreamHandler($filename, Logger::DEBUG));
        $logger->pushHandler(new FirePHPHandler());
        $logger->debug($message);
    }

    /**
     * Log warning into a file
     * @param string $message
     * @param string $file
     * @return void
     */
    public static function logWarning(string $message) : void
    {
        list($logPath, $logName) = sysa::getLogData();
        $logger = new Logger($logName);
        $filename = "$logPath/$logName-".date('Y-m').'.log';
        $logger->pushHandler(new StreamHandler($filename, Logger::WARNING));
        $logger->pushHandler(new FirePHPHandler());
        $logger->warning($message);
    }

    /**
     * Log error into a file
     * @param string $message
     * @param string $file
     * @return void
     */
    public static function logError(string $message) : void
    {
        list($logPath, $logName) = sysa::getLogData();
        $logger = new Logger($logName);
        $filename = "$logPath/$logName-".date('Y-m').'.log';
        $logger->pushHandler(new StreamHandler($filename, Logger::ERROR));
        $logger->pushHandler(new FirePHPHandler());
        $logger->error($message);
    }
}