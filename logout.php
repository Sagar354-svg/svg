<?php
session_start();

// Destroy the session to log out the user
session_unset();
session_destroy();

// Redirect to the login page after logging out
header("Location: index.php");  // Change to your login page
exit();
?>
