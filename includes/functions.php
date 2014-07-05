<?php

function redirect_to($new_location = null) {
    if (isset($new_location)) {
        header("Location: {$new_location}");
        exit;
    }
}

// Convert spaces in a string to underscores
function space_to_underscore($string) {
    $string = trim($string);

    // Make sure there are some spaces
    if (strpos($string, " ") > 0) {
        $string = str_replace(" ", "_", $string);
    }

    return $string;
}

// Check the database to see if a table already exists
function table_exists($table_name) {
    global $mysqli;

    $result = $mysqli->query("SHOW TABLES");
    $tables = array();

    // Store all table names in an array
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row["Tables_in_".DB_NAME];
    }

    // Search the array for a match
    if (in_array($table_name, $tables)) {
        return true;
    }

    return false;
}

// Create a new MySQL table
function create_table($table_name, $fields = array(), $primary_key) {
    global $mysqli;

    // Make sure all arguments are supplied
    if (isset($table_name) && isset($primary_key) && count($fields) && is_array($fields)) {
        $prefix = $db_prefix;
        $table_name = $prefix . space_to_underscore($table_name);

        // Make sure the table doesn't already exist
        if (!table_exists($table_name)) {
            // Build the query
            $query  = "CREATE TABLE IF NOT EXISTS {$table_name} (";
            foreach ($fields as $field) {
                $default = "";

                if (isset($field["default"])) {
                    $default = " DEFAULT '" . $field["default"] . "'";
                }

                $query .= $field["name"] . " " . $field["type"] . " " . $field["init"] . $default;

                if ($field["increment"] == "yes") {
                    $query .= " AUTO_INCREMENT";
                }

                $query .= ', ';
            }

            $query .= "PRIMARY KEY({$primary_key})";
            $query .= ")";

            // Execute the query
            $mysqli->query($query);
        }

        return $table_name;
    }

    return false;
}

function users_table() {
    $table = array(
        array("name" => "id",        "type" => "int",          "init" => "NOT NULL", "increment" => "yes"),
        array("name" => "username",  "type" => "varchar(255)", "init" => "NULL",     "increment" => "no"),
        array("name" => "password",  "type" => "varchar(60)",  "init" => "NULL",     "increment" => "no"),
        array("name" => "full_name", "type" => "varchar(255)", "init" => "NULL",     "increment" => "no"),
        array("name" => "join_date", "type" => "datetime",     "init" => "NULL",     "increment" => "no", "default" => "0000-00-00 00:00:00"),
        array("name" => "type",      "type" => "varchar(255)", "init" => "NOT NULL", "increment" => "no", "default" => "user")
    );

    return $table;
}

function user_exists($username) {
    global $mysqli;

    $result = $mysqli->query("SELECT username FROM ".DB_USER_TABLE." WHERE username = '{$username}'");

    if ($result->num_rows > 0) {
        return true;
    }

    return false;
}

function create_user($username, $full_name, $password, $type = "user") {
    global $mysqli;

    if (!user_exists($username)) {
        $join_date  = date("Y-m-d H:i:s");

        // Escape all strings
        $username   = $mysqli->real_escape_string(trim($username));
        $full_name  = $mysqli->real_escape_string(ucwords(trim($full_name)));

        $salt = "$2y$10$";

        for ($i = 0; $i < 22; $i++) {
            $salt .= substr("./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand(0, 63), 1);
        }

        $hash = crypt($password, $salt);

        // Build the query
        $query  = "INSERT INTO ".DB_USER_TABLE." (";
        $query .= "username, password, full_name, join_date, type";
        $query .= ") VALUES (";
        $query .= "'{$username}', '{$hash}', '{$full_name}', '{$join_date}', '{$type}')";

        // Execute the query
        $mysqli->query($query);

        return $username;
    }

    return false;
}

function delete_user($user_id) {
    global $mysqli;

    $mysqli->query("DELETE FROM ".DB_USER_TABLE." WHERE id = '{$user_id}'");
}

function user($username = "", $user_id, $output) {
    global $mysqli;

    switch ($output) {
        case "id":
            $content = $mysqli->query("SELECT id FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        case "full_name":
            $content = $mysqli->query("SELECT full_name FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        case "first_name":
            $content = $mysqli->query("SELECT full_name FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        case "last_name":
            $content = $mysqli->query("SELECT full_name FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        case "username":
            $content = $mysqli->query("SELECT username FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        case "password":
            $content = $mysqli->query("SELECT password FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        case "join_date":
            $content = $mysqli->query("SELECT join_date FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        case "type":
            $content = $mysqli->query("SELECT type FROM ".DB_USER_TABLE." WHERE id = '{$user_id}' OR username = '{$username}'");
            break;
        default: return false;
    }

    $content = $content->fetch_assoc();

    if ($output === "first_name") {
        if (strpos($output, " ") !== false) {
            return strstr($content["full_name"], " ", true);
        }

        return $content["full_name"];
    } elseif ($output === "last_name") {
        if (strpos($output, " ") !== false) {
            return strstr($content["full_name"], " ", false);
        }

        return $content["full_name"];
    }

    return $content[$output];
}

function login($username, $password) {
    if (user_exists($username)) {
        $hash = user($username, null, "password");

        if (crypt($password, $hash) === $hash) {
            // Store username and id in a session
            $_SESSION["username"] = $username;
            $_SESSION["user_id"]  = user($username, null, "id");

            return true;
        }
    }

    return false;
}

function is_user() {
    if (isset($_SESSION["username"])) {
        if (user($_SESSION["username"], null, "id") === $_SESSION["user_id"]) {
            if (user($_SESSION["username"], null, "type") === "user") {
                return true;
            }
        }
    }

    return false;
}

function is_admin() {
    if (isset($_SESSION["username"])) {
        if (user($_SESSION["username"], null, "id") === $_SESSION["user_id"]) {
            if (user($_SESSION["username"], null, "type") === "admin") {
                return true;
            }
        }
    }

    return false;
}

function logged_in() {
    return (is_admin() || is_user()) ? true : false;
}

function logout() {
    session_unset();
    session_destroy();
}
