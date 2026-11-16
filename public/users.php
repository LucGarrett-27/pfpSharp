<?php
// F2 - User Management (Admins only).
// Create users, give them roles, deactivate/reactivate them, reset passwords.
require_once __DIR__ . '/../includes/auth.php';

require_permission('view', 'users');      // everyone except Admins gets "Access denied" here

$errors = [];
$message = '';
$form = ['name' => '', 'email' => '', 'roles' => [], 'reviewer_level' => ''];

// ---------- helper functions ----------

function find_user(int $id): ?array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        return null;
    }
    $user['roles'] = parse_roles($user['role']);
    return $user;
}

// How many active Admins are there? (We never let this drop to zero.)
function active_admin_count(): int
{
    global $pdo;
    $count = 0;
    foreach ($pdo->query('SELECT role FROM users WHERE active = 1') as $row) {
        if (in_array('admin', parse_roles($row['role']), true)) {
            $count++;
        }
    }
    return $count;
}

// Read the role checkboxes and reviewer level from the form, and check them.
function roles_from_form(array &$errors): array
{
    $roles = array_values(array_intersect(array_keys(ROLES), (array) ($_POST['roles'] ?? [])));
    if (!$roles) {
        $errors[] = 'Choose at least one role.';
    }
    $level = (string) ($_POST['reviewer_level'] ?? '');
    if (in_array('reviewer', $roles, true)) {
        if (!in_array($level, REVIEWER_LEVELS, true)) {
            $errors[] = 'Reviewers need a level: OPSO, AOIC, or OIC.';
        }
    } else {
        $level = '';
    }
    return [$roles, $level === '' ? null : $level];
}

// ---------- handle form submissions ----------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('edit', 'users');
    check_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $form['name'] = trim((string) ($_POST['name'] ?? ''));
        $form['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
        $form['roles'] = (array) ($_POST['roles'] ?? []);
        $form['reviewer_level'] = (string) ($_POST['reviewer_level'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($form['name'] === '') {
            $errors[] = 'Enter a name.';
        }
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if (strlen($password) < 10) {
            $errors[] = 'The temporary password must be at least 10 characters.';
        }
        [$roles, $level] = roles_from_form($errors);

        if (!$errors) {
            $check = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
            $check->execute([$form['email']]);
            if ($check->fetchColumn()) {
                $errors[] = 'A user with that email already exists.';
            }
        }

        if (!$errors) {
            $pdo->prepare('INSERT INTO users (email, name, password_hash, role, reviewer_level, active) VALUES (?, ?, ?, ?, ?, 1)')
                ->execute([$form['email'], $form['name'], password_hash($password, PASSWORD_DEFAULT), implode(',', $roles), $level]);
            audit((int) $currentUser['id'], 'user_created', $form['email'] . ' as ' . implode(',', $roles));
            $message = 'Created ' . $form['name'] . ' (' . role_label($roles, $level) . ').';
            $form = ['name' => '', 'email' => '', 'roles' => [], 'reviewer_level' => ''];
        }

    } elseif (in_array($action, ['update', 'toggle_active', 'reset_password'], true)) {
        $target = find_user((int) ($_POST['user_id'] ?? 0));
        $isMe = $target && (int) $target['id'] === (int) $currentUser['id'];

        if (!$target) {
            $errors[] = 'That user no longer exists.';

        } elseif ($action === 'update') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                $errors[] = 'Enter a name.';
            }
            [$roles, $level] = roles_from_form($errors);

            // Don't let anyone lock everyone out of user management.
            $losingAdmin = in_array('admin', $target['roles'], true) && !in_array('admin', $roles, true);
            if ($losingAdmin && $isMe) {
                $errors[] = 'You cannot remove your own Admin role. Ask another Admin to do it.';
            } elseif ($losingAdmin && (int) $target['active'] === 1 && active_admin_count() <= 1) {
                $errors[] = 'There must always be at least one active Admin.';
            }

            if (!$errors) {
                $pdo->prepare('UPDATE users SET name = ?, role = ?, reviewer_level = ? WHERE id = ?')
                    ->execute([$name, implode(',', $roles), $level, $target['id']]);
                audit((int) $currentUser['id'], 'user_updated', $target['email'] . ' roles=' . implode(',', $roles));
                $message = 'Updated ' . ($name ?: $target['email']) . '.';
            } else {
                $_GET['edit'] = $target['id'];     // keep the edit form open so they can fix it
            }

        } elseif ($action === 'toggle_active') {
            $makeActive = (int) $target['active'] === 1 ? 0 : 1;

            if (!$makeActive && $isMe) {
                $errors[] = 'You cannot deactivate your own account.';
            } elseif (!$makeActive && in_array('admin', $target['roles'], true) && active_admin_count() <= 1) {
                $errors[] = 'There must always be at least one active Admin.';
            } else {
                $pdo->prepare('UPDATE users SET active = ? WHERE id = ?')->execute([$makeActive, $target['id']]);
                audit((int) $currentUser['id'], $makeActive ? 'user_reactivated' : 'user_deactivated', $target['email']);
                $message = ($makeActive ? 'Reactivated ' : 'Deactivated ') . ($target['name'] ?: $target['email']) . '.';
            }

        } else { // reset_password
            $password = (string) ($_POST['password'] ?? '');
            if (strlen($password) < 10) {
                $errors[] = 'The new temporary password must be at least 10 characters.';
                $_GET['edit'] = $target['id'];
            } else {
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $target['id']]);
                audit((int) $currentUser['id'], 'password_reset', $target['email']);
                $message = 'Password reset for ' . ($target['name'] ?: $target['email']) . '.';
            }
        }

    } else {
        $errors[] = 'Unknown action.';
    }
}

// ---------- load data for the page ----------

$users = $pdo->query('SELECT * FROM users ORDER BY active DESC, name, email')->fetchAll();
foreach ($users as &$user) {
    $user['roles'] = parse_roles($user['role']);
}
unset($user);
$editing = isset($_GET['edit']) ? find_user((int) $_GET['edit']) : null;

// Small helpers to draw the role checkboxes and the reviewer-level dropdown.
function role_checkboxes(array $checked, string $idPrefix): void
{
    foreach (ROLES as $value => $label) {
        $id = $idPrefix . '-' . $value; ?>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="roles[]" value="<?= h($value) ?>" id="<?= h($id) ?>"
                <?= in_array($value, $checked, true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="<?= h($id) ?>"><?= h($label) ?></label>
        </div>
    <?php }
}

function level_select(?string $selected, string $id): void
{ ?>
    <select class="form-select" name="reviewer_level" id="<?= h($id) ?>">
        <option value="">— Only for Reviewers —</option>
        <?php foreach (REVIEWER_LEVELS as $level): ?>
            <option value="<?= h($level) ?>" <?= $selected === $level ? 'selected' : '' ?>><?= h($level) ?></option>
        <?php endforeach; ?>
    </select>
<?php }

$pageTitle = 'User Management';
require __DIR__ . '/../includes/topbar.php';
?>
    <main class="page-shell">
        <div class="welcome-row">
            <div>
                <div class="eyebrow">Administration</div>
                <h1 class="page-title">User Management</h1>
                <p class="welcome-copy">Create accounts, assign roles, and deactivate users. Only Admins can see this page.</p>
            </div>
            <a class="back-link-dark" href="dashboard.php">← Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="notice notice-ok mb-4" role="status"><span aria-hidden="true">✓</span>
                <div><strong>Saved</strong><?= h($message) ?></div>
            </div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="notice notice-error mb-4" role="alert"><span aria-hidden="true">⚠</span>
                <div><strong>Please fix the following</strong><?= implode('<br>', array_map('h', $errors)) ?></div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-xl-4">
                <?php if ($editing): ?>
                    <div class="card-box">
                        <h2>Edit <?= h($editing['name'] ?: $editing['email']) ?></h2>
                        <form method="post" action="users.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?= (int) $editing['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label" for="edit-name">Name</label>
                                <input class="form-control" id="edit-name" name="name" value="<?= h($editing['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <span class="form-label d-block">Email</span>
                                <span class="text-secondary"><?= h($editing['email']) ?></span>
                            </div>
                            <div class="mb-3">
                                <span class="form-label d-block">Roles</span>
                                <?php role_checkboxes($editing['roles'], 'edit-role'); ?>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="edit-level">Reviewer level</label>
                                <?php level_select($editing['reviewer_level'], 'edit-level'); ?>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn-gold" type="submit">Save changes</button>
                                <a class="btn-outline-navy" href="users.php">Cancel</a>
                            </div>
                        </form>
                        <hr>
                        <form method="post" action="users.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="user_id" value="<?= (int) $editing['id'] ?>">
                            <label class="form-label" for="reset-pw">Set a new temporary password</label>
                            <div class="d-flex gap-2">
                                <input class="form-control" type="password" id="reset-pw" name="password" minlength="10" autocomplete="new-password" required>
                                <button class="btn-outline-navy" type="submit">Reset</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card-box">
                        <h2>Create a user</h2>
                        <form method="post" action="users.php" data-testid="create-user-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="create">
                            <div class="mb-3">
                                <label class="form-label" for="new-name">Name</label>
                                <input class="form-control" id="new-name" name="name" value="<?= h($form['name']) ?>" placeholder="ENS Jane Doe" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="new-email">Email</label>
                                <input class="form-control" type="email" id="new-email" name="email" value="<?= h($form['email']) ?>" placeholder="name@usna.edu" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="new-password">Temporary password</label>
                                <input class="form-control" type="password" id="new-password" name="password" minlength="10" autocomplete="new-password" required>
                                <div class="form-text">At least 10 characters. Give it to the user in person.</div>
                            </div>
                            <div class="mb-3">
                                <span class="form-label d-block">Roles</span>
                                <?php role_checkboxes($form['roles'], 'new-role'); ?>
                                <div class="form-text">A person can hold more than one role.</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="new-level">Reviewer level</label>
                                <?php level_select($form['reviewer_level'] ?: null, 'new-level'); ?>
                            </div>
                            <button class="btn-gold w-100 justify-content-center" type="submit">Create user</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card-box">
                    <h2>All users (<?= count($users) ?>)</h2>
                    <div class="table-responsive">
                        <table class="users-table">
                            <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($users as $user): ?>
                                <?php $isMe = (int) $user['id'] === (int) $currentUser['id']; ?>
                                <tr class="<?= (int) $user['active'] === 1 ? '' : 'inactive' ?>" data-email="<?= h($user['email']) ?>">
                                    <td><?= h($user['name'] ?: '—') ?><?= $isMe ? ' <span class="text-secondary">(you)</span>' : '' ?></td>
                                    <td><?= h($user['email']) ?></td>
                                    <td data-roles="<?= h(implode(',', $user['roles'])) ?>">
                                        <?php foreach ($user['roles'] as $role): ?>
                                            <span class="role-chip"><?= h(role_label([$role], $user['reviewer_level'])) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <span class="status-pill <?= (int) $user['active'] === 1 ? 'status-active' : 'status-inactive' ?>">
                                            <?= (int) $user['active'] === 1 ? 'Active' : 'Deactivated' ?>
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <a class="btn-outline-navy" href="users.php?edit=<?= (int) $user['id'] ?>">Edit</a>
                                        <?php if (!$isMe): ?>
                                            <form method="post" action="users.php" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="toggle_active">
                                                <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                                <button class="btn-outline-navy <?= (int) $user['active'] === 1 ? 'btn-danger-soft' : '' ?>" type="submit">
                                                    <?= (int) $user['active'] === 1 ? 'Deactivate' : 'Reactivate' ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
