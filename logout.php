<?php
// logout.php

session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Optionally, delete the "remember_me" cookie if implemented
/*
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, "/", "", true, true);
}
*/

// Redirect to the login page with a success message
header("Location: login.php?success=" . urlencode("You have been logged out successfully."));
exit();
?>