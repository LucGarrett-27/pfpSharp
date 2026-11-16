<?php

require_once __DIR__ . '/session.php';

// Check whether the user has an authenticated session.
if (!isset($_SESSION['user_id'])) {

    // Redirect unauthenticated visitors.
    header('Location: login.html');
    exit();
}
?>