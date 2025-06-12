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
            throw new ErrorException("Query \"" . $prq->queryString . "\" failed");
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
            throw new ErrorException("Query failed");
        }

        return $query->fetchAll();
    }

    private function getUserBy(string $colName, mixed $value): UserDTO|false
    {
        $prq = $this->pdo->prepare("SELECT * FROM users WHERE $colName = :$colName");
        $prq->bindValue(":$colName", $value);
        $prq->setFetchMode(
            PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
            UserDTO::class
        );

        if (!$prq->execute()) {
            throw new ErrorException("Query \"" . $prq->queryString . "\" failed");
        }

        return $prq->fetch();
    }

    public function getUserById(int $id): UserDTO|false
    {
        return $this->getUserBy("id", $id);
    }

    private function getUserByEmail(string $email): UserDTO|false
    {
        return $this->getUserBy("email", $email);
    }

    private function updateUserBy(string $colName, mixed $value, UserDTO $user): UserDTO
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
                throw new ErrorException("Query \"" . $prq->queryString . "\" failed");
            }
        }

        return $user;
    }

    public function updateUserById(int $id, UserDTO $user): UserDTO
    {
        return $this->updateUserBy("id", $id, $user);
    }

    public function deleteUserById(int $id): UserDTO
    {
        $user = $this->getUserById($id);

        $prq = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        if (!$prq->execute([$id])) {
            throw new ErrorException("Query \"" . $prq->queryString . "\" failed");
        }

        return $user;
    }
}
