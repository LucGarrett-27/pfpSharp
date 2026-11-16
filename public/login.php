<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/permissions.php';

// Only accept form submissions (someone opening login.php directly goes back to the form).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit();
}

// Get submitted credentials
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header('Location: login.html?error=invalid');
    exit();
}

try {

    // Find the registered user by email.
    $stmt = $pdo->prepare("
        SELECT id, email, password_hash, role, active
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch();

    // Verify the password AND that the account is active.
    // A deactivated account gets the same message as a wrong password.
    if ($user && password_verify($password, $user['password_hash']) && (int) $user['active'] === 1) {

        // Generate a new session ID after successful login.
        session_regenerate_id(true);

        // Store authenticated user information.
        // (Role is re-read from the database on every page in includes/auth.php.)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        audit((int) $user['id'], 'login', $user['email']);

        // Redirect to dashboard.
        header('Location: dashboard.php');
        exit();

    } else {

        audit($user ? (int) $user['id'] : null, 'login_failed', $email);

        // Do not reveal whether the email or password was incorrect.
        header('Location: login.html?error=invalid');
        exit();
    }

} catch (PDOException $e) {

    // Log the actual error without exposing database details.
    error_log('Login database error: ' . $e->getMessage());

    http_response_code(500);
    exit('A server error occurred. Please try again later.');

}
