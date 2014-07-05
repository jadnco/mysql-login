<?php

session_name("user_session");
session_start();

require_once("config.php");

// Get the MySQL connection
require_once("connect.php");

// All the main functions
require_once("functions.php");
