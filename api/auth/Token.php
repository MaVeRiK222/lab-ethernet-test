<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/src/Database.php';

class Token
{
    private static string $server_salt = 'osNbO4U9h6nlFatPAI8ufsP2il5A9Tz8';

    private static function hashToken($plain_token)
    {
        return hash('sha256', $plain_token . Token::$server_salt);
    }

    public static function generateToken()
    {
        $random_string = bin2hex(random_bytes(32));
        return [
            'plain_token' => $random_string,
            'hash_token' => Token::hashToken($random_string),
            'expires_at' => time() + 3600
        ];
    }

    public static function storeToken($hash_token, $user_id, $expires_at){
        $sql_query = "INSERT INTO tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
        $params = [$user_id, $hash_token, $expires_at];
        $var_types_string = 'isi';
        Database::getInstance();
        try {
            $result = Database::execute($sql_query, $var_types_string, $params);
        } catch(Exception $e){
            echo 'Не сохранили';
        }
        return $result;
    }

    public static function getUserToken($user_id)
    {
        $sql_query = 'SELECT token, expires_at FROM tokens JOIN users ON users.id = tokens.user_id WHERE users.id = ?';
        $params = [$user_id];
        $var_types_string = 'i';
        Database::getInstance();
        $result = Database::execute($sql_query, $var_types_string, $params);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public static function isTokenValid($plain_token, $user_id)
    {
        $calculated_hash_token = Token::hashToken($plain_token);
        $stored_hash_token = Token::getUserToken($user_id);
        $isValid = false;
        foreach($stored_hash_token as $item){
            $isValid |= hash_equals($item['token'], $calculated_hash_token) && time() < $item['expires_at'];
        }
        return $isValid;
    }
}