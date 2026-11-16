<?php
// Workspace page (replaces unimplementedFeatureModule.html).
// Shows whether this user can view only, or view and edit, and checks edits on the server.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modules.php';

$key = (string) ($_GET['module'] ?? '');

if ($key === 'users') {             // user management has its own page
    header('Location: users.php');
    exit();
}
if (isset(MODULES[$key]['link'])) {  // workspaces that have their own page (e.g. Schedule.php)
    header('Location: ' . MODULES[$key]['link']);
    exit();
}
if (!isset(MODULES[$key])) {
    deny(404, 'Page not found', 'That workspace does not exist.');
}

require_permission('view', $key);
$module = MODULES[$key];
$canEdit = can('edit', $key);
$message = '';

// Pressing the edit button sends a POST. Check permission HERE, on the server:
// a Reviewer who sends this request without the button still gets "Access denied".
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('edit', $key);   // first: is this user allowed to edit at all?
    check_csrf();                       // then: did the request come from our own form?
    audit((int) $currentUser['id'], 'edit_' . $key, 'edit placeholder');
    $message = 'Edit access confirmed. The editing tools for this workspace come in a later sprint.';
}

$pageTitle = $module['title'];
require __DIR__ . '/../includes/topbar.php';
?>
    <main class="shell">
        <section class="panel">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div class="eyebrow">Workspace</div>
                <?php if ($canEdit): ?>
                    <span class="access-badge access-edit" data-access="edit">You can view and edit</span>
                <?php else: ?>
                    <span class="access-badge access-view" data-access="view">View only</span>
                <?php endif; ?>
            </div>
            <h1 class="title"><?= h($module['title']) ?></h1>
            <p class="desc"><?= h($module['description']) ?></p>
            <div class="divider"></div>

            <?php if ($message): ?>
                <div class="notice notice-ok mb-3" role="status"><span aria-hidden="true">✓</span>
                    <div><strong>Done</strong><?= h($message) ?></div>
                </div>
            <?php endif; ?>

            <div class="notice"><span aria-hidden="true">ⓘ</span>
                <div><strong>This feature is not built yet</strong>This placeholder confirms the dashboard navigation
                    and your access level work. The next step is to build the tools and connect them to your data.</div>
            </div>

            <div class="button-row">
                <a class="btn-home" href="dashboard.php">← Return to dashboard</a>
                <?php if ($canEdit && $module['edit_label']): ?>
                    <form method="post" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-gold" data-testid="edit-button"><?= h($module['edit_label']) ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>

</html>
