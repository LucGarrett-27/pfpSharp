<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/permissions.php';

if (isset($_SESSION['user_id'])) {
    audit((int) $_SESSION['user_id'], 'logout');
}

// Clear session variables
$_SESSION = [];
// Delete the session cookie
setcookie(session_name(), '', time() - 3600, '/');
session_destroy();
header("Location: login.html");
exit();
