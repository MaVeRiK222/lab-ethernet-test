<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/src/Database.php';

class ApiController
{

    public static function getUser($id)
    {
        if (is_numeric($id)) {
            $sql_query = "SELECT * FROM users WHERE id=?";
            $params = [$id];
            $result = Database::execute($sql_query, 'i', $params, 'SELECT');
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
        $result = Database::execute($sql_query, 'sss', $params, 'INSERT');
        return ApiController::getResponse(['created_id' => $result], 201, 'Создание успешно');
    }

    public static function updateUser($id, $data, $request_type)
    {
        $users_columns_list = ['login', 'password_hash', 'email'];
        $sql_query = "UPDATE users SET ";
        $params = [];
        $var_types_string = "";
        $data['password_hash'] = password_hash($data['password_hash'], PASSWORD_DEFAULT);
        $update_string = '';
        if ($request_type == "PATCH"){

            $k = 0;
            foreach($data as $key => $value){
                    $k++;
                    $params[] = $value;
                    $update_string .= $key . '=?';
                    $update_string .= $k !== (count($data)) ? ', ' : ' ';

                    if(is_string($value)) $var_types_string .= 's';
                    elseif(is_numeric($value)) $var_types_string .= 'i';
            }
        }elseif($request_type== "PUT"){
            foreach($users_columns_list as $key => $column){
                $value = $data[$column];
                $params[] = empty($value)? null: $value;
                $update_string .= $column . '=?';
                $update_string .= $key !== (count($users_columns_list) - 1) ? ', ' : ' ';

                if(is_string($value)) {$var_types_string .= 's';}
                elseif(is_numeric($value)) $var_types_string .= 'i';
                elseif(is_null($value)){$var_types_string .= 's';}
            }
        }
        echo is_string($params[0]) ? 'Да' : "Нет";
        $params[] = $id;
        $var_types_string .= 'i';
        $sql_query .= $update_string . "WHERE id=?";
        echo $sql_query;
        print_r($params);
        echo $var_types_string;
        $affected_rows = Database::execute($sql_query, $var_types_string, $params, 'UPDATE');
        return $affected_rows > 0 ? ApiController::getResponse($affected_rows, 200, 'Изменение успешно'):
            ApiController::getResponse($affected_rows, 200, 'Не было изменено ни одной строки');
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