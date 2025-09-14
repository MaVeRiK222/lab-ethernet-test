<?php
require_once __DIR__ . '/api/src/init.php';

ini_set('display_errors', 0);
ini_set('error_log', 1);
ini_set('error_log', __DIR__ . '/logs/route.errors.log');

logStart();

$url_arr = explode('/', $_GET['q']);
$request_method = $_SERVER['REQUEST_METHOD'];
$raw_data = file_get_contents('php://input');
$data = !empty($raw_data) ? json_decode($raw_data, 1) : null;

$user_token = getallheaders()['my-custom-token'];
$user_id = $_COOKIE['user_id'];

Logger::log('Запрос от пользователя', 'route.log', $_REQUEST);
Logger::log('Тип запроса', 'route.log', $_SERVER['REQUEST_METHOD']);

switch ($url_arr[0]) {
    case 'api':
        require_once __DIR__ . '/api/controllers/ApiController.php';
        if ($url_arr[1] === 'users') {
            switch ($request_method) {
                case "GET":
                    if (!empty($url_arr[2])) {
                        try {
                            if (!ApiController::isUserAuth($user_token, $user_id)) {
                                echo response(401, 'Not Authorized');
                                logEnd();
                                exit;
                            }
                            $id = $url_arr[2];

                            $response = ApiController::getUser($id);
                        } catch (Exception $e) {
                            Logger::log('Ошибка при получении пользователя', 'route.log', $e->getMessage());
                            response(500, 'Server Error');
                        }
                        echo $response;
                    } else {
                        echo response(404, 'Not Found');
                    }
                    break;
                case "POST":
                    try {
                        if (empty($url_arr[2])) {

                            $login = $data['login'];
                            $pass = $data['pass'];
                            $email = $data['email'] ?? '';

                            if (!(Validator::isEmailValid($email) && Validator::isLoginValid($login) && Validator::isPassValid($pass))) {
                                echo response(200, 'Данные не прошли валидацию');
                                logEnd();
                                return;
                            }
                            $response = ApiController::createUser($login, $pass, $email);
                            echo $response;
                        } else {
                            echo response(404, 'Not Found');
                        }
                    } catch (Exception $e) {
                        Logger::log('Ошибка при создании пользователя', 'route.log', $e->getMessage());
                        response(500, 'Server Error');
                    }
                    break;
                case "PATCH":
                case "PUT":
                    try {
                        if (!empty($url_arr[2])) {

                            if (!ApiController::isUserAuth($user_token, $user_id)) {
                                echo response(401, 'Not Authorized');
                                exit;
                            }
                            $login = $data['login'] ?? null;
                            $email = $data['email'] ?? null;
                            $pass = $data['pass'] ?? null;
                            $age = $data['age'] ?? null;

                            if (($request_method === "PATCH" && !isPatchValid($email, $login, $pass, $age)) ||
                            $request_method === "PUT" && !isPutValid($email, $login, $pass, $age)){
                                response(200, 'Неверные данные');
                                logEnd();
                                exit;
                            }
                            $id = $url_arr[2];

                            $response = is_numeric($id) ? ApiController::updateUser($id, $data, $request_method) : null;
                            echo $response;
                        } else {
                            echo response(404, 'Not Found');
                        }
                    } catch (Exception $e) {
                        Logger::log('Ошибка при обновлении пользователя', 'route.log', $e->getMessage());
                        response(500, 'Server Error');
                    }
                    break;
                case "DELETE":
                    try {
                        if (!empty($url_arr[2])) {

                            if (!ApiController::isUserAuth($user_token, $user_id)) {
                                echo response(401, 'Not Authorized');
                                exit;
                            }

                            $id = $url_arr[2];
                            $response = is_numeric($id) ? ApiController::deleteUser($id) : null;
                            echo $response;
                        } else {
                            echo response(404, 'Not Found');
                        }
                    } catch (Exception $e) {
                        Logger::log('Ошибка при удалении пользователя', 'route.log', $e->getMessage());
                        response(500, 'Server Error');
                    }
                    break;
                default:
                    response(404, 'Not Found');
            }
        } else {
            response(404, 'Not Found');
        }
        break;
    case 'login':
        try {
            require_once __DIR__ . '/api/controllers/ApiController.php';

            $data = ApiController::login($data['login'], $data['pass']);
            if (empty($data)) {echo response(200, 'Неверные данные'); exit;}
            setcookie('user_id', $data['user_id'], time() + 60 * 30, '/');
            $response = json_encode(['code' => 200, 'token' => $data['token']]);
            echo $response;
        } catch (Exception $e) {
            Logger::log('Ошибка при попытке входа', 'route.log', $e->getMessage());
            response(500, 'Server Error');
        }
        break;

    default:
        response(404, 'Not Found');

}

logEnd();

function response($code, $message)
{
    echo $message;
    http_response_code($code);
}

function isPatchValid($email = null, $login = null, $pass = null, $age = null){
     return ((Validator::isEmailValid($email) || $email === null) &&
         (Validator::isLoginValid($login) || $login === null) &&
         (Validator::isPassValid($pass) || $pass === null) &&
        (Validator::isAgeValid($age) || $age === null));

}

function isPutValid($email, $login, $pass, $age = null){
    return (Validator::isEmailValid($email) &&
        Validator::isLoginValid($login) &&
        Validator::isPassValid($pass) &&
        (Validator::isAgeValid($age) || $age == null));

}

function logStart()
{
    Logger::log('---------------START---------------', 'route.log');
}

function logEnd()
{
    Logger::log('-----------END-----------', 'route.log');
}