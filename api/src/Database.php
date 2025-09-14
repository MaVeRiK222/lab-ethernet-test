<?php

class Database
{
    protected static $instance = null;

    private function __construct()
    {
        if (self::$instance !== null) {
            return self::$instance;
        } else {
            self::$instance = Database::init();
        }
    }

    private static function init(){
        $connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $connect->set_charset(DB_CHARSET);
        $connect->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
        return $connect;
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = Database::init();
        return self::$instance;
    }

    public static function execute($query, $var_types_string, $params)
    {
        $stmt = self::$instance->prepare($query);
        if (is_bool($stmt) && !$stmt) {
            throw new Exception("Prepare failed: " . self::$instance->error . " | Query: " . $query);
        }
        $bind_bool = $stmt->bind_param($var_types_string, ...$params);
        if (!$bind_bool) {
            throw new Exception("Bind param failed: " . self::$instance->error . " | Query: " . $query);
        }
        $execute_bool = $stmt->execute();
        if (!$execute_bool) {
            throw new Exception("Execute failed: " . self::$instance->error . " | Query: " . $query);
        }
        $query_type = strtoupper(strtok(trim($query), " "));
        switch (mb_convert_case($query_type, MB_CASE_UPPER)) {
            case('SELECT'):
                $response = $stmt->get_result();
                for ($result = array(); $row = $response->fetch_assoc(); $result[] = $row) ;
                break;
            case('INSERT'):
                $result = $stmt->insert_id;
                break;
            case('UPDATE'):
            case('DELETE'):
                $result = $stmt->affected_rows;
                break;
        }
        $stmt->close();
        return $result;
    }

    public static function executeMigration($query){
        $stmt = self::$instance->prepare($query);
        $stmt->execute();
    }
    public static function exit()
    {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }
}