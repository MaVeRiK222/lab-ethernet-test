<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/src/Validator.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/auth/Token.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/src/Database.php';

$url_arr = explode('/', $_GET['q']);
$request_method = $_SERVER['REQUEST_METHOD'];
$raw_data = file_get_contents('php://input');
$data = !empty($raw_data) ? json_decode($raw_data, 1) : null;

switch ($url_arr[0]) {
    case 'api':
        require_once 'api/controllers/ApiController.php';
        switch ($request_method) {
            case "GET":
                if (!empty($url_arr[2])) {
                    $user_token = $_SERVER['HTTP_MY_CUSTOM_TOKEN'];
                    $user_id = $_COOKIE['user_id'];
                    if(!ApiController::isUserAuth($user_token, $user_id)){
                        echo response(401, 'Not Authorized');
                        exit;
                    }
                    $id = $url_arr[2];
                    $response = ApiController::getUser($id);
                    echo $response;
                } else {
                    echo response(404, 'Not Found');
                }
                break;
            case "POST":
                if (empty($url_arr[2])) {

                    print_r($data);
                    $login = $data['login'];
                    $pass = $data['pass'];
                    $email = $data['email'] ?? '';

                    if (!(Validator::isEmailValid($email) && Validator::isLoginValid($login) && Validator::isPassValid($pass))) {
                        echo response(200, 'Данные не прошли валидацию');
                        echo $_ENV['serverSalt'];
                        return;
                    }
                    $response = ApiController::createUser($login, $pass, $email);
                    echo $response;
                } else {
                    echo response(404, 'Not Found');
                }
                break;
            case "PATCH":
//                if (!empty($url_arr[2])) {
//                    $id = $url_arr[2];
//                    is_numeric($id) ? ApiController::updateUser($id, $data) : null;
//                }
//                break;
            case "PUT":
                if (!empty($url_arr[2])) {
                    $id = $url_arr[2];

                    $response = is_numeric($id) ? ApiController::updateUser($id, $data, $request_method) : null;
                    echo $response;
                } else {
                    echo response(404, 'Not Found');
                }

                break;
            case "DELETE":
                if (!empty($url_arr[2])) {
                    $id = $url_arr[2];
                    $result = is_numeric($id) ? ApiController::deleteUser($id) : null;
                    echo $result;
                } else {
                    echo response(404, 'Not Found');
                }
                break;
            default:
                response(404, 'Not Found');
        }
        break;
    case 'login':
        require_once 'api/controllers/ApiController.php';

        $data = ApiController::login($data['login'], $data['pass']);
        if (empty($data)) echo response(200, 'Неверные данные');
        setcookie('user_id', $data['user_id']);
        $json = json_encode(['token' => $data['token']]);
        echo $json;

        break;

    default:
        response(404, 'Not Found');

}


function response($code, $message)
{
    echo $message;
    http_response_code($code);
}