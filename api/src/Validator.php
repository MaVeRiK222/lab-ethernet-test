<?php

class Validator
{
    public static function isEmailValid($email)
    {
        if (empty($email)) return false;
        return preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $email);
    }

    public static function isLoginValid($login)
    {
        if (empty($login)) return false;
        return preg_match('/^[a-z0-9]{3,50}$/', $login);
    }

    public static function isPassValid($password)
    {
        if (empty($password)) return false;
        return preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\'"\\\\|,.<>\/?~])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};:\'"\\\\|,.<>\/?~]{6,}$/', $password);
    }

    public static function isAgeValid($age)
    {
        if (empty($age)) return false;
        return is_numeric($age) && $age >= 0 && $age < 100;
    }
}