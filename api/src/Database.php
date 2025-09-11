<?php

class Database
{
    private static $db;

    private static function init()
    {
        if (!empty(self::$db)) return;
        self::$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        self::$db->set_charset(DB_CHARSET);
        self::$db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
    }

    public static function execute($query, $var_types_string, $params)
    {
        Database::init();
        $stmt = self::$db->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . self::$db->error . " | Query: " . $query);
        }
        $stmt->bind_param($var_types_string, ...$params);
        if (!$stmt) {
            throw new Exception("Bind param failed: " . self::$db->error . " | Query: " . $query);
        }
        $stmt->execute();
        if (!$stmt) {
            throw new Exception("Execute failed: " . self::$db->error . " | Query: " . $query);
        }
        $query_type = strtoupper(strtok(trim($query), " "));
        switch ($query_type) {
            case('SELECT'):
                $result = $stmt->get_result();
                self::exit();
                return $result;
            case('INSERT'):
                $result = $stmt->insert_id;
                self::exit();
                return $result;
            case('UPDATE'):
            case('DELETE'):
                $result = $stmt->affected_rows;
                self::exit();
                return $result;
        }
    }

    private static function exit()
    {
        if (!empty(self::$db)) {
            self::$db->close();
        }
    }
}