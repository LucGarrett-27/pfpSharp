<?php
// Shared <head> and top navigation bar for every signed-in page.
// Set $pageTitle before including this file.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PFP Sharp | <?= h($pageTitle ?? 'Dashboard') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/styles.css" />
</head>

<body>
    <nav class="topbar" aria-label="Main navigation">
        <a class="brand" href="dashboard.php"><span class="brand-icon">P</span><span>PFP SHARP</span></a>
        <div class="topbar-right">
            <?php if (!empty($currentUser)): ?>
                <span class="user-chip">
                    <span class="user-email"><?= h($currentUser['email']) ?></span>
                    <span class="user-role" data-role="<?= h(implode(',', $currentUser['roles'])) ?>"><?= h(role_label($currentUser['roles'], $currentUser['reviewer_level'])) ?></span>
                </span>
            <?php endif; ?>
            <a class="logout" href="logout.php">Sign out ↗</a>
        </div>
    </nav>
