<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Ren\App\Connect;
use Ren\App\DTO\UserDTO;
use Ren\App\Services\UserService;
use Ren\App\Utils;

session_start();

$exception = null;
$exceptionMessege = null;
$user = array_key_exists("user", $_SESSION) ? $_SESSION["user"] : null;
$userService = new UserService(Connect::connectPDO());
$users = $userService->getUsers();

try {
    if (array_key_exists("action", $_POST)) {
        $incomingUser = Utils::createUserFromPost();
        if ($_POST["action"] === "update") {
            $user = $userService->updateUserByEmail($user->email, $incomingUser);
            $_SESSION["user"] = $user;
        } elseif ($_POST["action"] === "create_user") {
            $userService->createUser($incomingUser);
            $users = $userService->getUsers();
        } elseif ($_POST["action"] === "update_user") {
            $userService->updateUserBy("id", $incomingUser->id, $incomingUser);
            $users = $userService->getUsers();
        } elseif ($_POST["action"] === "delete_user") {
            $userService->deleteUserByEmail($incomingUser->email);
            $users = $userService->getUsers();
        }
    }
} catch (Exception $e) {
    $exception = true;
    $exceptionMessege = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
            <fieldset class="flex-content">
                <legend>Admin</legend>
                <div class="flex-row">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required value="<?php echo $user->email ?? "" ?>">
                </div>
                <div class="flex-row">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password">
                </div>
                <div class="flex-row">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="<?php echo $user->name ?? "" ?>">
                </div>
                <div class="flex-row">
                    <label for="surname">Surname</label>
                    <input type="text" name="surname" id="surname" value="<?php echo $user->surname ?? "" ?>">
                </div>
                <input type="text" name="action" id="action" value="update" hidden>
                <input type="submit" value="Submit">
            </fieldset>
        </form>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
            <fieldset class="flex-content">
                <legend>Create User</legend>
                <div class="flex-row">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="flex-row">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="flex-row">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" required>
                </div>
                <div class="flex-row">
                    <label for="surname">Surname</label>
                    <input type="text" name="surname" id="surname" required>
                </div>
                <input type="text" name="action" id="action" value="create_user" hidden>
                <input type="submit" value="Submit">
            </fieldset>
        </form>
    </div>

    <?php if (!empty($users))
        foreach ($users as $user): ?>
        <div class="flex-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
                <fieldset class="flex-content">
                    <legend><?php echo $user->name ?></legend>
                    <div class="flex-row">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required value="<?php echo $user->email ?? "" ?>">
                    </div>
                    <div class="flex-row">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password">
                    </div>
                    <div class="flex-row">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="<?php echo $user->name ?? "" ?>">
                    </div>
                    <div class="flex-row">
                        <label for="surname">Surname</label>
                        <input type="text" name="surname" id="surname" value="<?php echo $user->surname ?? "" ?>">
                    </div>
                    <div class="flex-row">
                        <input type="radio" name="action" value="update_user" checked>
                        <label for="update">update</label>
                        <input type="radio" name="action" value="delete_user">
                        <label for="delete">delete</label>
                    </div>
                    <input type="hidden" name="id" value="<?php echo $user->id ?>">
                    <input type="submit" value="Submit">
                </fieldset>
            </form>
        </div>
    <?php endforeach ?>
    <?php if ($exception): ?>
        <p><?php echo $exceptionMessege ?></p>
    <?php endif ?>
</body>

</html>