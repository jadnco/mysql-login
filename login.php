<?php

// Require init file
require_once("includes/init.php");

// Redirect to root if already logged in
if (logged_in()) redirect_to(".");

?>

<?php

if (isset($_POST["login_submit"])) {
    $username = trim($_POST["username"]);
	$password = trim($_POST["password"]);

	if (empty($username) || empty($password)) {
    	$error = "Fill both fields.";
	} else {
        $login = login($username, $password);

        if ($login === true) {
            redirect_to(".");
        } else {
            $error = "Couldn't login.";
        }
	}
}

?>

<form id="login-form" class="clearfix" method="post">
    <input type="text" name="username" placeholder="Username" value="<?=isset($_POST["username"]) ? $_POST["username"] : ""?>">
    <input type="password" name="password" placeholder="Password">
    <?php if (isset($error)) { ?>
        <span class="login-error"><?=$error?></span>
    <?php } ?>
    <input type="submit" name="login_submit" value="Login">
</form>
