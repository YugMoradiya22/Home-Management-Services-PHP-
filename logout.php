<?php
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Destroy the session completely
session_destroy();

// 3. Redirect back to the Home page
header("Location: Homes.php");
exit;
