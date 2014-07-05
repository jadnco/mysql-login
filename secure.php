<?php

require_once("includes/init.php");

// Redirect to login page if not logged in
if (!logged_in()) redirect_to("login.php");

?>

<div>You are logged in.</div>
