<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Ren\App\Connect;
use Ren\App\Services\UserService;
use Ren\App\Utils;

session_start();

$exception = null;
$exceptionMessege = null;
$user = array_key_exists("user", $_SESSION) ? $_SESSION["user"] : null;
$userService = new UserService(Connect::connectPDO());

try {
    if (array_key_exists("action", $_POST)) {
        if ($_POST["action"] === "update") {
            $updateUser = Utils::createUserFromPost();
            $user = $userService->updateUserById($user->id, $updateUser);
            $_SESSION["user"] = $user;
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
    <title>User</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
            <fieldset class="flex-content">
                <legend>User</legend>
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
                <input type="hidden" name="id" value="<?php echo $user->id ?>">
                <input type="submit" value="Update">
            </fieldset>
        </form>
    </div>

    <?php if ($exception): ?>
        <p><?php echo $exceptionMessege ?></p>
    <?php endif ?>
</body>

</html>