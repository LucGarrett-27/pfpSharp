<?php
// F2 - User Accounts & Roles
// Every role and what it is allowed to do lives in this one file.
// To change who can do what, edit PERMISSIONS below. Nothing else needs to change.

// The roles a user can have. Key = what is stored in the database, value = what people see.
const ROLES = [
    'admin'     => 'Admin',
    'scheduler' => 'Scheduler',
    'reviewer'  => 'Reviewer',
];

// Reviewers approve the schedule in this order.
const REVIEWER_LEVELS = ['OPSO', 'AOIC', 'OIC'];

// Who can VIEW and who can EDIT each workspace (the dashboard tiles).
// A user with two roles (for example "scheduler,reviewer") gets everything either role allows.
const PERMISSIONS = [
    'deconfliction' => ['view' => ['admin', 'scheduler', 'reviewer'], 'edit' => ['admin', 'scheduler']],
    'schedule'      => ['view' => ['admin', 'scheduler', 'reviewer'], 'edit' => ['scheduler']],
    'students'      => ['view' => ['admin', 'scheduler', 'reviewer'], 'edit' => ['admin', 'scheduler']],
    'aircraft'      => ['view' => ['admin', 'scheduler', 'reviewer'], 'edit' => ['admin', 'scheduler']],
    'reports'       => ['view' => ['admin', 'scheduler', 'reviewer'], 'edit' => []],
    'users'         => ['view' => ['admin'],                          'edit' => ['admin']],
];

// Turn the role column ("scheduler,reviewer") into a list (['scheduler', 'reviewer']).
function parse_roles(?string $roleColumn): array
{
    $roles = array_map('trim', explode(',', strtolower((string) $roleColumn)));
    return array_values(array_intersect(array_keys(ROLES), $roles));
}

// Readable label, e.g. "Scheduler, Reviewer (OPSO)".
function role_label(array $roles, ?string $reviewerLevel = null): string
{
    $labels = [];
    foreach ($roles as $role) {
        $label = ROLES[$role];
        if ($role === 'reviewer' && $reviewerLevel) {
            $label .= " ($reviewerLevel)";
        }
        $labels[] = $label;
    }
    return implode(', ', $labels);
}

// Can the logged-in user do $action ('view' or 'edit') in $area (e.g. 'schedule')?
function can(string $action, string $area): bool
{
    global $currentUser;
    if (empty($currentUser) || !isset(PERMISSIONS[$area][$action])) {
        return false;
    }
    return count(array_intersect($currentUser['roles'], PERMISSIONS[$area][$action])) > 0;
}

// Stop the page with "Access denied" unless the user is allowed.
// IMPORTANT: always check on the server. Hiding a button is not enough,
// because anyone can type the URL or send the form themselves.
function require_permission(string $action, string $area): void
{
    global $currentUser;
    if (!can($action, $area)) {
        audit($currentUser['id'] ?? null, 'access_denied', "$action $area");
        deny(403, 'Access denied', 'You do not have access to this page. If you need it, ask an Admin to change your role.');
    }
}

// Show an error page with the site's look and stop.
function deny(int $code, string $title, string $message): void
{
    global $currentUser;          // so the top bar can show who is signed in
    http_response_code($code);
    $pageTitle = $title;
    require __DIR__ . '/topbar.php';
    ?>
    <main class="shell">
        <section class="panel">
            <div class="eyebrow">Error <?= $code ?></div>
            <h1 class="title"><?= h($title) ?></h1>
            <div class="notice notice-error"><span aria-hidden="true">&#9888;</span>
                <div><strong><?= h($title) ?></strong><?= h($message) ?></div>
            </div>
            <a class="btn-home" href="dashboard.php">&larr; Return to dashboard</a>
        </section>
    </main>
    </body>
    </html>
    <?php
    exit();
}

// Escape text before putting it in HTML (stops script injection).
function h(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// ---------- CSRF protection ----------
// Every form that changes data includes a secret token, and the server checks it.
// This stops another website from submitting our forms using a logged-in user's session.
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function check_csrf(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        deny(400, 'Form expired', 'Your session expired. Go back, reload the page, and try again.');
    }
}

// ---------- Audit log: a record of who did what ----------
function audit(?int $userId, string $action, string $detail = ''): void
{
    global $pdo;
    try {
        $pdo->prepare('INSERT INTO audit_log (user_id, action, detail) VALUES (?, ?, ?)')
            ->execute([$userId, $action, $detail]);
    } catch (PDOException $e) {
        error_log('Audit log error: ' . $e->getMessage());   // never block the user because logging failed
    }
}
