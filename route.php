<?php
require_once 'api/config/db.php';
$url_arr = explode('/', $_GET['q']);
//print_r($url_arr);
//echo $_SERVER['REQUEST_METHOD'];
$request_method = $_SERVER['REQUEST_METHOD'];
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, 1);
switch ($url_arr[0]) {
    case 'api':
        require_once 'api/controllers/ApiController.php';
        switch ($request_method) {
            case "GET":
                if (!empty($url_arr[2])) {
                    $id = $url_arr[2];
                    $response = ApiController::getUser($id);
                    echo $response;
                }
                break;
            case "POST":
                if (empty($url_arr[2])) {

                    print_r($data);
                    $login = $data['login'];
                    $pass = $data['pass'];
                    $email = $data['email'] ?? '';
                    $response = ApiController::createUser($login, $pass, $email);
                    echo $response;
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
                }

                break;
            case "DELETE":
                if (!empty($url_arr[2])) {
                    $id = $url_arr[2];
                    $result = is_numeric($id) ? ApiController::deleteUser($id) : null;
                    echo $result;
                }
                break;
            default:
                response(404, 'Not Found');
        }
        break;
    default:
        response(404, 'Not Found');

}


function response($code, $message)
{
    echo $message;
    http_response_code($code);
}