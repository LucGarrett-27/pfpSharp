<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// Get submitted credentials
$email = $_POST["email"];
$password = $_POST["password"];

if ($email === '' || $password === '') {
    header('Location: login.html?error=invalid');
    exit();
}

//database stuff here done by AI everything else coded
try {

    // Find the registered user by email.
    $stmt = $pdo->prepare("
        SELECT id, email, password_hash, role
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch();

    // Verify the submitted password against the stored hash.
    if ($user && password_verify($password, $user['password_hash'])) {

        // Generate a new session ID after successful login.
        session_regenerate_id(true);

        // Store authenticated user information.
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Redirect to dashboard.
        header('Location: dashboard.php');
        exit();

    } else {

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

?>