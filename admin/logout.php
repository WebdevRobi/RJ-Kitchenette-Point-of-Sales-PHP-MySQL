<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to the login page or home page outside the admin folder
header("Location: ../index.php"); // Adjust the path as needed (../ takes you out of the admin folder)
exit();
?>
``
