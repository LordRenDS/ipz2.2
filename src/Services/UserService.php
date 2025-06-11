<?php

namespace Ren\App\Services;

use ErrorException;
use PDO;
use Ren\App\DTO\UserDTO;
use Ren\App\Exception\UnauthoraizeException;

require_once __DIR__ . '/../../vendor/autoload.php';

class UserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function loginUser(string $email, string $password): UserDTO
    {
        $user = $this->getUserByEmail($email);
        if ($user === false || !password_verify($password, $user->password)) {
            throw new UnauthoraizeException("Unauthoraize");
        }
        return $user;
    }

    public function createUser(UserDTO $user): UserDTO
    {
        $prq = $this->pdo->prepare("INSERT INTO users (email, password, name, surname)"
            . " VALUES (:email, :password, :name, :surname)");

        $password = password_hash($user->password, PASSWORD_BCRYPT);

        $prq->bindValue(":email", $user->email);
        $prq->bindValue(":name", $user->name);
        $prq->bindValue(":surname", $user->surname);
        $prq->bindValue(":password", $password);

        if (!$prq->execute()) {
            throw new ErrorException("User with email = " . $user->email . " dose not register");
        }

        return $this->getUserByEmail($user->email);
    }

    public function getUsers(): array
    {
        $query = $this->pdo->query(
            "SELECT * FROM users WHERE admin = 0",
            PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
            UserDTO::class
        );

        if ($query === false) {
            throw new ErrorException();
        }

        return $query->fetchAll();
    }

    public function getUserByEmail(string $email): UserDTO|false
    {
        $prq = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $prq->bindValue(":email", $email);
        $prq->setFetchMode(
            PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
            UserDTO::class
        );

        if (!$prq->execute()) {
            throw new ErrorException("User with email = " . $email . " not found");
        }

        return $prq->fetch();
    }

    public function updateUserBy(string $colName, mixed $value, UserDTO $user): UserDTO
    {
        $updates = [];
        $params = [];

        if (isset($user->email)) {
            $updates[] = "email = :email";
            $params[":email"] = $user->email;
        }
        if (isset($user->password) && $user->password !== "") {
            $updates[] = "password = :password";
            $params[":password"] = password_hash($user->password, PASSWORD_BCRYPT);
        }
        if (isset($user->name)) {
            $updates[] = "name = :name";
            $params[":name"] = $user->name !== "" ? $user->name : null;
        }
        if (isset($user->surname)) {
            $updates[] = "surname = :surname";
            $params[":surname"] = $user->surname !== "" ? $user->surname : null;
        }

        if (!empty($updates)) {
            $byColName = "by_" . $colName;
            $params[":" . $byColName] = $value;

            $prq = $this->pdo->prepare("UPDATE users SET " . implode(", ", $updates) . " WHERE $colName = :$byColName");

            if (!$prq->execute($params)) {
                throw new ErrorException("User with $colName = $value not updated");
            }
        }

        return $user;
    }

    public function updateUserByEmail(string $email, UserDTO $user): UserDTO
    {
        return $this->updateUserBy("email", $email, $user);
    }

    //     public function updateUserByEmail(string $email, UserDTO $user): UserDTO
    // {
    //     $updates = [];
    //     $params = [];

    //     if (isset($user->email)) {
    //         $updates[] = "email = :email";
    //         $params[":email"] = $user->email;
    //     }
    //     if (isset($user->password) && $user->password !== "") {
    //         $updates[] = "password = :password";
    //         $params[":password"] = password_hash($user->password, PASSWORD_BCRYPT);
    //     }
    //     if (isset($user->name)) {
    //         $updates[] = "name = :name";
    //         $params[":name"] = $user->name !== "" ? $user->name : null;
    //     }
    //     if (isset($user->surname)) {
    //         $updates[] = "surname = :surname";
    //         $params[":surname"] = $user->surname !== "" ? $user->surname : null;
    //     }

    //     if (!empty($updates)) {
    //         $params[":current_email"] = $email;

    //         $prq = $this->pdo->prepare("UPDATE users SET " . implode(", ", $updates) . " WHERE email = :current_email");

    //         if (!$prq->execute($params)) {
    //             throw new ErrorException("User with email = " . $email . " not updated");
    //         }
    //     }

    //     return $user;
    // }

    public function deleteUserByEmail(string $email): UserDTO
    {
        $user = $this->getUserByEmail($email);

        $prq = $this->pdo->prepare("DELETE FROM users WHERE email = ?");
        if (!$prq->execute([$email])) {
            throw new ErrorException("Failed to delete user with email = " . $email . ".");
        }

        return $user;
    }
}
