<?php

$pageTitle =
    $pageTitle ?? 'Dynamic Resume';


function renderNavigation(
    string $mode = 'standard'
): void {
?>

<nav class="resume-nav">

    <?php if ($mode === 'portfolio'): ?>

        <a href="#objective">
            Profile
        </a>

        <a href="#education">
            Education
        </a>

        <a href="#experience">
            Experience
        </a>

        <a href="#projects">
            Projects
        </a>

        <a href="#skills">
            Skills
        </a>

    <?php else: ?>

        <a href="index.php">
            Portfolio
        </a>

    <?php endif; ?>


    <?php if (!empty($_SESSION['user_id'])): ?>

        <a
            href="admin.php"
            class="admin-link"
        >
            Admin Dashboard
        </a>

        <?php if ($mode !== 'portfolio'): ?>

            <a href="logout.php">
                Logout
            </a>

        <?php endif; ?>

    <?php else: ?>

        <a
            href="login.php"
            class="admin-link"
        >
            Admin Login
        </a>

    <?php endif; ?>

</nav>

<?php
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= escape($pageTitle) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="page">