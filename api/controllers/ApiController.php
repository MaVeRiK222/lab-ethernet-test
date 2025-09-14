<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../auth/Token.php';
require_once __DIR__ . '/../src/Validator.php';

class ApiController
{

    public static function getUser($id)
    {
        if (is_numeric($id)) {
            $sql_query = "SELECT id, login, email, age FROM users WHERE id=?";
            $params = [$id];
            Database::getInstance();
            $result = Database::execute($sql_query, 'i', $params);
            return ApiController::getResponse($result);
        }
        return ApiController::getResponse(null, 400, 'id ресурса должен быть числом');
    }

    public static function createUser($login, $pass, $email, $age=null)
    {
        $cant_create = false;
        $message = 'Такой пользователь уже существует.';
        if (self::isUserEmailExists($email)) {
            $message .= " Смените почту.";
            $cant_create = true;
        }
        if (self::isUserLoginExists($login)) {

            $cant_create = true;
            $message .= " Смените логин.";
        }
        if ($cant_create) return ApiController::getResponse([], 200, $message);

        $sql_query = "INSERT INTO users (login, password_hash, email, age) VALUES (?, ?, ?, ?)";
        $hash_pass = password_hash($pass, PASSWORD_DEFAULT);
        $params = [$login, $hash_pass, $email, $age];
        Database::getInstance();
        $result = Database::execute($sql_query, 'ssss', $params);
        return ApiController::getResponse(['created_id' => $result], 201, 'Создание успешно');
    }

    public static function updateUser($id, $data, $request_type)
    {
        $users_columns_list = ['login', 'password_hash', 'email', 'age'];
        $pass = $data['pass'] ?? null;
        $sql_query = "UPDATE users SET ";
        $params = [];
        $var_types_string = "";
        if (!empty($pass)) {
            $data['password_hash'] = password_hash($pass, PASSWORD_DEFAULT);
            unset($data['pass']);
        }
        $update_string = '';
        if ($request_type == "PATCH") {
            $k = 0;
            foreach ($data as $key => $value) {
                $k++;
                $params[] = $value;
                $update_string .= $key . '=?';
                $update_string .= $k !== (count($data)) ? ', ' : ' ';

                if (is_string($value)) $var_types_string .= 's';
                elseif (is_numeric($value)) $var_types_string .= 'i';
            }
        } elseif ($request_type == "PUT") {
            foreach ($users_columns_list as $key => $column) {
                $value = $data[$column];
                $params[] = empty($value) ? null : $value;
                $update_string .= $column . '=?';
                $update_string .= $key !== (count($users_columns_list) - 1) ? ', ' : ' ';

                if (is_string($value)) {
                    $var_types_string .= 's';
                } elseif (is_numeric($value)) $var_types_string .= 'i';
                elseif (is_null($value)) {
                    $var_types_string .= 's';
                }
            }
        }
        $params[] = $id;
        $var_types_string .= 'i';
        $sql_query .= $update_string . "WHERE id=?";
        Database::getInstance();
        $affected_rows = Database::execute($sql_query, $var_types_string, $params);
        return $affected_rows > 0 ? ApiController::getResponse($affected_rows, 200, 'Изменение успешно') :
            ApiController::getResponse($affected_rows, 200, 'Не было изменено ни одной строки');
    }

    public static function deleteUser($id)
    {
        $sql_query = 'DELETE FROM users WHERE id=?';
        $params = [$id];
        Database::getInstance();
        $affected_rows = Database::execute($sql_query, 'i', $params);
        return $affected_rows > 0 ? ApiController::getResponse($affected_rows, 204, 'Удаление успешно') :
            ApiController::getResponse($affected_rows, 404, 'Не было удалено ни одной строки');
    }

    public static function login($login, $pass)
    {
        $sql_query = 'SELECT id, password_hash FROM users WHERE login = ?';
        $params = [$login];
        $var_types_string = 's';
        Database::getInstance();
        $result = Database::execute($sql_query, $var_types_string, $params);
        $verifyPass = false;
        $result = array_pop($result);

        $verifyPass |= password_verify($pass, $result['password_hash']);
        if (!$verifyPass) return null;

        $token = Token::generateToken();
        Token::storeToken($token['hash_token'], $result['id'], $token['expires_at']);
        return [
            'user_id' => $result['id'],
            'token' => $token['plain_token']
        ];
    }

    public static function isUserAuth($token, $user_id)
    {
        if (empty($token) || empty($user_id)) return false;
        return Token::isTokenValid($token, $user_id);
    }

    private static function isUserLoginExists($login)
    {
        $sql = "SELECT login FROM users WHERE login=?";
        $params = [$login];
        $var_types_string = 's';
        Database::getInstance();
        $result = Database::execute($sql, $var_types_string, $params);
        return !empty($result);
    }

    private static function isUserEmailExists($email)
    {
        $sql = "SELECT email FROM users WHERE email=?";
        $params = [$email];
        $var_types_string = 's';
        Database::getInstance();
        $result = Database::execute($sql, $var_types_string, $params);
        return !empty($result);
    }

    private static function getResponse($data = [], $code = 200, $message = '')
    {
        $response = ['code' => $code];
        if (empty($data)) {
            $response['data'] = [];
        } else {
            $response['data'] = $data;
        }
        if (!empty($message)) {
            $response['message'] = $message;
        }

        return json_encode($response);
    }

}