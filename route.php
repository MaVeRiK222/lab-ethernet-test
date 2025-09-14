<?php
require_once __DIR__ . '/api/src/init.php';

ini_set('display_errors', 0);
ini_set('error_log', 1);
ini_set('error_log', __DIR__ . '/logs/route.errors.log');

logStart();

$url_arr = explode('/', $_GET['q']);
$request_method = $_SERVER['REQUEST_METHOD'];

Logger::log('Запрос от пользователя', 'route.log', $_REQUEST);
Logger::log('Тип запроса', 'route.log', $_SERVER['REQUEST_METHOD']);

switch ($url_arr[0]) {
    case 'api':
        require_once __DIR__ . '/routes/users.php';
        break;
    case 'login':
        require_once __DIR__ . '/routes/login.php';
        break;

    default:
        Response::getJsonResponse(404, 'Not Found');

}

logEnd();


function logStart()
{
    Logger::log('---------------START---------------', 'route.log');
}

function logEnd()
{
    Logger::log('-----------END-----------', 'route.log');
}