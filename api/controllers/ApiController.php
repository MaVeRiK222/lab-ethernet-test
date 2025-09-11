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
        return ApiController::getResponse(['created_id' => $result], 201, 'Создание успешно');
    }

    public static function updateUser($id, $data)
    {
    }

    public static function deleteUser($id)
    {
        $sql_query = 'DELETE FROM users WHERE id=?';
        $params = [$id];
        $affected_rows = Database::execute($sql_query, 'i', $params);
        return $affected_rows > 0 ? ApiController::getResponse($affected_rows, 200, 'Удаление успешно'):
            ApiController::getResponse($affected_rows, 200, 'Не было удалено ни одной строки');
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