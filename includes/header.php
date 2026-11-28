<?php
$pageTitle = $pageTitle ?? 'Dynamic Resume';

// Shared navigation: section links on the portfolio, simple actions on CMS pages.
function renderNavigation(bool $portfolio = false): void
{
    if ($portfolio): ?>
<nav class="resume-nav">
    <a href="#objective">Profile</a>
    <a href="#education">Education</a>
    <a href="#experience">Experience</a>
    <a href="#projects">Projects</a>
    <a href="#skills">Skills</a>
    <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="admin.php" class="admin-link">Admin Dashboard</a>
    <?php else: ?>
        <a href="login.php" class="admin-link">Admin Login</a>
    <?php endif; ?>
</nav>
    <?php elseif (!empty($_SESSION['user_id'])): ?>
    <div class="admin-actions">
        <a href="index.php">View Portfolio</a>
        <form method="post" action="logout.php" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
            <button type="submit" class="link-button">Logout</button>
        </form>
    </div>
    <?php else: ?>
        <div class="admin-actions"><a href="index.php">← Back to Portfolio</a></div>
    <?php endif;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page">
