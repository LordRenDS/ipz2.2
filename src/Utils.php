<?php

namespace Ren\App;

use ErrorException;
use Ren\App\DTO\UserDTO;

class Utils
{
    public static function clearInput($string)
    {
        $string = trim($string);
        $string = stripslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }

    public static function createUserFromPost(): UserDTO
    {
        Utils::checkPostForUser();
        return new UserDTO(
            array_key_exists("id", $_POST) ? $_POST["id"] : null,
            Utils::clearInput($_POST["name"]),
            Utils::clearInput($_POST["surname"]),
            Utils::clearInput($_POST["email"]),
            Utils::clearInput($_POST["password"]),
        );
    }

    public static function checkPostForUser(): void
    {
        if (
            !array_key_exists("name", $_POST) ||
            !array_key_exists("surname", $_POST) ||
            !array_key_exists("email", $_POST) ||
            !array_key_exists("password", $_POST)
        ) {
            throw new ErrorException("Undefined user fields");
        }
    }
}
