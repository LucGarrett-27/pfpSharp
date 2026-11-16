<?php

require_once __DIR__ . '/../includes/session.php';

// Clear session variables
$_SESSION = [];
// Delete the session cookie
setcookie(
    session_name(),
    '',
    time() - 3600,
    '/'
);
session_destroy();
header("Location: login.html");
exit();

?>