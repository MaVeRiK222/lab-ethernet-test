<?php

require_once __DIR__ . '/../api/src/init.php';

$url_arr = explode('/', $_GET['q']);
$request_method = $_SERVER['REQUEST_METHOD'];
$raw_data = file_get_contents('php://input');
$data = !empty($raw_data) ? json_decode($raw_data, 1) : null;

try {
    $data = ApiController::login($data['login'], $data['pass']);
    if (empty($data)) {
        echo Response::getJsonResponse(200, 'Неверные данные');
        exit;
    }
    setcookie('user_id', $data['user_id'], time() + 60 * 30, '/');
    $response = json_encode(['code' => 200, 'token' => $data['token']]);
    echo $response;
} catch (Exception $e) {
    Logger::log('Ошибка при попытке входа', 'route.log', $e->getMessage());
    Response::getJsonResponse(500, 'Server Error');
}