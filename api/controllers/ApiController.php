<?php
require_once $_SERVER['DOCUMENT_ROOT']. '/api/src/Database.php';
Class ApiController {

    public static function getUser($id){
        $sql_query = "SELECT * FROM users WHERE id=?";
        $params = [$id];
        $result = Database::execute($sql_query, 'i', $params);
        for($user = array(); $data = $result->fetch_assoc(); $user[] = $data);
        echo json_encode($user);
    }


}