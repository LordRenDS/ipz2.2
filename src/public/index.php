<?php

use Ren\App\Connect;
use Ren\App\Exception\UnauthoraizeException;
use Ren\App\Services\UserService;
use Ren\App\Utils;

require_once __DIR__ . '/../../vendor/autoload.php';

session_start();

$exception = null;
$exceptionMessege = null;

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $userService = new UserService(Connect::connectPDO());
        $email = Utils::clearInput($_POST["email"]);
        $password = Utils::clearInput($_POST["password"]);

        $user = $userService->loginUser($email, $password);
        $_SESSION["user"] = $user;

        $redirect = "Location: ";
        if ($user->admin) {
            $redirect .= "/admin.php";
        } else {
            $redirect .= "/user.php";
        }
        header($redirect);
    }
} catch (Exception $e) {
    $exception = true;
    if ($e instanceof UnauthoraizeException) {
        $exceptionMessege = "Зв'яжіться з адміністратором або зареєструйтесь.";
    } else {
        $exceptionMessege = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
            <fieldset class="flex-content">
                <legend>Login</legend>
                <div class="flex-row">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="flex-row">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <input type="submit" value="Submit">
            </fieldset>
        </form>
    </div>

    <?php if ($exception): ?>
        <p><?php echo $exceptionMessege ?></p>
    <?php endif ?>
</body>

</html>