<?php

require_once __DIR__ . '/../api/src/init.php';

$url_arr = explode('/', $_GET['q']);
$request_method = $_SERVER['REQUEST_METHOD'];
$raw_data = file_get_contents('php://input');
$data = !empty($raw_data) ? json_decode($raw_data, 1) : null;

$user_token = getallheaders()['my-custom-token'];
$user_id = $_COOKIE['user_id'];

if ($url_arr[1] === 'users') {
    switch ($request_method) {
        case "GET":
            if (!empty($url_arr[2])) {
                try {
                    if (!ApiController::isUserAuth($user_token, $user_id)) {
                        echo Response::getJsonResponse(401, 'Not Authorized');
                        logEnd();
                        exit;
                    }
                    $id = $url_arr[2];

                    $response = ApiController::getUser($id);
                } catch (Exception $e) {
                    Logger::log('Ошибка при получении пользователя', 'route.log', $e->getMessage());
                    Response::getJsonResponse(500, 'Server Error');
                }
                echo $response;
            } else {
                echo Response::getJsonResponse(404, 'Not Found');
            }
            break;
        case "POST":
            try {
                if (empty($url_arr[2])) {

                    $login = $data['login'];
                    $pass = $data['pass'];
                    $email = $data['email'] ?? null;
                    $age = $data['age'] ?? null;

                    echo (Validator::isLoginValid($login));
                    if (!(Validator::isEmailValid($email) &&
                        Validator::isLoginValid($login) &&
                        Validator::isPassValid($pass))) {
                        echo Response::getJsonResponse(200, 'Данные не прошли валидацию');
                        logEnd();
                        return;
                    }
                    $response = ApiController::createUser($login, $pass, $email, $age);
                    echo $response;
                } else {
                    echo Response::getJsonResponse(404, 'Not Found');
                }
            } catch (Exception $e) {
                Logger::log('Ошибка при создании пользователя', 'route.log', $e->getMessage());
                Response::getJsonResponse(500, 'Server Error');
            }
            break;
        case "PATCH":
        case "PUT":
            try {
                if (!empty($url_arr[2])) {

                    if (!ApiController::isUserAuth($user_token, $user_id)) {
                        echo Response::getJsonResponse(401, 'Not Authorized');
                        exit;
                    }
                    $login = $data['login'] ?? null;
                    $email = $data['email'] ?? null;
                    $pass = $data['pass'] ?? null;
                    $age = $data['age'] ?? null;

                    if (($request_method === "PATCH" && !isPatchValid($email, $login, $pass, $age)) ||
                        $request_method === "PUT" && !isPutValid($email, $login, $pass, $age)){
                        Response::getJsonResponse(200, 'Неверные данные');
                        logEnd();
                        exit;
                    }
                    $id = $url_arr[2];

                    $response = is_numeric($id) ? ApiController::updateUser($id, $data, $request_method) : null;
                    echo $response;
                } else {
                    echo Response::getJsonResponse(404, 'Not Found');
                }
            } catch (Exception $e) {
                Logger::log('Ошибка при обновлении пользователя', 'route.log', $e->getMessage());
                Response::getJsonResponse(500, 'Server Error');
            }
            break;
        case "DELETE":
            try {
                if (!empty($url_arr[2])) {

                    if (!ApiController::isUserAuth($user_token, $user_id)) {
                        echo Response::getJsonResponse(401, 'Not Authorized');
                        exit;
                    }

                    $id = $url_arr[2];
                    $response = is_numeric($id) ? ApiController::deleteUser($id) : null;
                    echo $response;
                } else {
                    echo Response::getJsonResponse(404, 'Not Found');
                }
            } catch (Exception $e) {
                Logger::log('Ошибка при удалении пользователя', 'route.log', $e->getMessage());
                Response::getJsonResponse(500, 'Server Error');
            }
            break;
        default:
            Response::getJsonResponse(404, 'Not Found');
    }
} else {
    Response::getJsonResponse(404, 'Not Found');
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