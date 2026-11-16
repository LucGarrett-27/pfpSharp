<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/permissions.php';

// Check whether the user has an authenticated session.
if (!isset($_SESSION['user_id'])) {

    // Redirect unauthenticated visitors.
    header('Location: login.html');
    exit();
}

// Look the user up again on every page, so that if an Admin deactivates
// someone or changes their role, it takes effect on their very next click.
$stmt = $pdo->prepare('SELECT id, email, name, role, reviewer_level, active FROM users WHERE id = :id');
$stmt->execute([':id' => $_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser || (int) $currentUser['active'] !== 1) {

    // The account was deleted or deactivated: end the session.
    $_SESSION = [];
    session_destroy();
    header('Location: login.html');
    exit();
}

// e.g. ['scheduler', 'reviewer']
$currentUser['roles'] = parse_roles($currentUser['role']);
