<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modules.php';

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/topbar.php';
?>
    <main class="page-shell">
        <div class="welcome-row">
            <div>
                <div class="eyebrow">Operations workspace</div>
                <h1 class="page-title">Dashboard</h1>
                <p class="welcome-copy">Choose a workspace to get started with PFP Sharp.</p>
            </div>
            <div class="date-chip" id="todayLabel">Your scheduling workspace</div>
        </div>
        <div class="section-heading">
            <h2>Applications</h2><span>Choose a tile to open a workspace</span>
        </div>
        <div class="row g-4">
            <?php foreach (MODULES as $key => $module): ?>
                <?php if (!can('view', $key)) continue; // only show tiles this user's role can open ?>
                <?php $link = $module['link'] ?? (($key === 'users') ? 'users.php' : 'module.php?module=' . $key); ?>
                <div class="col-12 col-sm-6 col-xl-4">
                    <a class="module-card" href="<?= h($link) ?>" aria-label="Open <?= h($module['title']) ?>" data-module="<?= h($key) ?>">
                        <div class="module-image-wrap"><img src="<?= h($module['image']) ?>" alt="" class="module-image"
                                loading="lazy"><span class="image-arrow" aria-hidden="true">↗</span></div>
                        <div class="module-copy"><span class="module-tag"><?= h($module['tag']) ?></span>
                            <h2><?= h($module['title']) ?></h2>
                            <p><?= h($module['description']) ?></p><span
                                class="module-link">Open workspace <span aria-hidden="true">→</span></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="footer-note">PFP Sharp prototype. You see the workspaces your role allows
            (<?= h(role_label($currentUser['roles'], $currentUser['reviewer_level'])) ?>).</p>
    </main>
    <script>
        const today = new Date();
        document.getElementById('todayLabel').textContent = today.toLocaleDateString(undefined, {weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'});
    </script>
</body>

</html>
