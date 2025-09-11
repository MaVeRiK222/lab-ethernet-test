<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/src/Database.php';

class ApiController
{

    public static function getUser($id)
    {
        if (is_numeric($id)) {
            $sql_query = "SELECT * FROM users WHERE id=?";
            $params = [$id];
            $result = Database::execute($sql_query, 'i', $params);
            for ($user = array(); $data = $result->fetch_assoc(); $user[] = $data) ;
            if(count($user)=== 1) $user = array_pop($user);
            return ApiController::getResponse($user);
        }
        return ApiController::getResponse(null, 400, 'id ресурса должен быть числом');
    }

    public static function createUser($login, $pass, $email = '')
    {
        $sql_query = "INSERT INTO users (login, password_hash, email) VALUES (?, ?, ?)";
        $hash_pass = password_hash($pass, PASSWORD_DEFAULT);
        $params = [$login, $hash_pass, $email];
        $result = Database::execute($sql_query, 'sss', $params);
        echo $result->insert_id;
        return ApiController::getResponse();
    }

    public static function updateUser($id, $data)
    {
    }

    public static function deleteUser($id)
    {
    }

    public static function login($login, $hash_pass){

    }

    private static function getResponse($data = [], $code = 200, $message = '')
    {
        $response = ['code' => $code];
        if (empty($data)) {
            $response['data'] = 'empty';
        } else {
            $response['data'] = $data;
        }
        if (!empty($message)) {
            $response['message'] = $message;
        }

        return json_encode($response);
    }

}