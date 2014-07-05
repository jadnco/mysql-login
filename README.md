# PHP & MySQL Login System

In order for this script to work you must require the `init.php` file.

    require_once("includes/init.php");

To make a page secure, simply paste the following code right after the `require`

    if (!logged_in()) redirect_to("login.php");

Alternatively, you can check whether someone is an admin by calling `is_admin()` or user `is_user()`; both return a bool.

    if (is_admin() || is_user()) {
        echo "Logged In as ", is_admin() ? "admin" : "user";
    } else {
        echo "Not Logged In";
    }
