<?php

// Open a new connection instance; use constants from the config.php
$mysqli = new mysqli("localhost", DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_errno) {
    echo "Database connection error.";
    exit;
}
