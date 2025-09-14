<?php

class Response
{
    public static function getJsonResponse ($code, $message='', $data = []){
        http_response_code($code);
        $response_arr = ['code'=>$code];
        if(!empty($message)) $response_arr['message'] = $message;
        if(!empty($data)) $response_arr['data'] = $data;
        return json_encode($response_arr);
    }
}