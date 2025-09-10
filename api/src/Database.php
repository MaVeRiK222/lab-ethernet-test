<?php

class Database
{
    private static $db;

    private static function init()
    {
        if(!empty(self::$db)) return;
        self::$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        self::$db->set_charset(DB_CHARSET);
        self::$db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
    }

    public static function execute($query, $var_types_string, $params){
        Database::init();
        $stmt = self::$db->prepare($query);
        $id = 2;
        $stmt->bind_param($var_types_string, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        self::exit();
        return $result;
    }
    private static function exit(){
        if(!empty(self::$db)){
            self::$db->close();
        }
    }
}