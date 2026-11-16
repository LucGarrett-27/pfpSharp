<?php
// F2 database update. Safe to run more than once.
// Run it ONCE on the server from the project folder:
//     php database/migrate.php
//
// It adds to the existing users table (keeps everyone's accounts and passwords):
//   name            - person's name, shown in user management
//   reviewer_level  - OPSO, AOIC, or OIC (only for reviewers)
//   active          - 1 = can log in, 0 = deactivated
// and creates audit_log, a record of logins and account changes.

if (PHP_SAPI !== 'cli') {      // never run from a web browser
    http_response_code(404);
    exit();
}

require __DIR__ . '/../config/database.php';

$columns = array_column($pdo->query('PRAGMA table_info(users)')->fetchAll(), 'name');

$add = [
    'name'           => "ALTER TABLE users ADD COLUMN name TEXT NOT NULL DEFAULT ''",
    'reviewer_level' => 'ALTER TABLE users ADD COLUMN reviewer_level TEXT',
    'active'         => 'ALTER TABLE users ADD COLUMN active INTEGER NOT NULL DEFAULT 1',
];
foreach ($add as $column => $sql) {
    if (!in_array($column, $columns, true)) {
        $pdo->exec($sql);
        echo "Added users.$column\n";
    }
}

// Store roles in lowercase so they match includes/permissions.php.
$pdo->exec("UPDATE users SET role = lower(trim(role))");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS audit_log (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER REFERENCES users (id),
        action      TEXT NOT NULL,
        detail      TEXT,
        created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

$count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
echo "Database is up to date. $count user(s) kept.\n";
