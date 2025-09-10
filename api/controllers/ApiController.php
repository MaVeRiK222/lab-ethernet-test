<?php
require_once $_SERVER['DOCUMENT_ROOT']. '/api/src/Database.php';
Class ApiController {

    public static function getUser($id){
        $sql_query = "SELECT * FROM users WHERE id=?";
        $params = [$id];
        $result = Database::execute($sql_query, 'i', $params);
        for($user = array(); $data = $result->fetch_assoc(); $user[] = $data);
        echo ApiController::getResponse($user, 200);
    }

    public static function createUser($login, $pass, $email =''){}

    private static function getResponse ($data, $code){
        $response = ['code' => $code];
        if(empty($data)){
            $response['data'] = 'empty';
        }
        else {$response['data'] = $data;}
        return json_encode($response);
    }

}