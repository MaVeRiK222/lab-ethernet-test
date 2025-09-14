<?php

class Logger
{

    private static function getLogRootPath()
    {
        return __DIR__ . '/../logs/';
    }

    private static function checkFileSize($file_path)
    {
        if (file_exists($file_path)) {
            if (filesize($file_path) > 10 * 1024 * 1024) {
                file_put_contents($file_path, '');
            }
        }
    }

    public static function log( $message, $file_name = 'log.log', $data = [])
    {
        $file_path = self::getLogRootPath() . $file_name;
        self::checkFileSize($file_path);
        $log_message = date('D M d H:i:s Y',time()) . " | " . $message . " | ";
        $log_message .= !empty($data) ? print_r($data,1) . PHP_EOL : PHP_EOL;
        file_put_contents($file_path, $log_message, FILE_APPEND);
    }

}