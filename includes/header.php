<?php
require_once __DIR__ . '/config.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>SPD Jobs Inc. Bataan</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/img/favicon.svg">
</head>
<body>

<nav class="navbar">
    <a href="<?= SITE_URL ?>/index.php" class="nav-logo" style="text-decoration:none;">
        <div class="nav-logo-box"><span>SPD</span></div>
        <div>
            <div class="nav-brand-name">SPD Jobs Inc.</div>
            <div class="nav-brand-sub">Bataan Branch</div>
        </div>
    </a>
    <div class="nav-links">
        <a href="<?= SITE_URL ?>/index.php">Home</a>
        <a href="<?= SITE_URL ?>/jobs.php">Browse Jobs</a>
        <a href="<?= SITE_URL ?>/index.php#about">About</a>
        <a href="<?= SITE_URL ?>/index.php#contact">Contact</a>
    </div>
    <div class="nav-user">
        <?php if (isLoggedIn()): ?>
            <div class="nav-avatar"><?= strtoupper(substr($_SESSION['user_firstname'] ?? 'U', 0, 1) . substr($_SESSION['user_lastname'] ?? '', 0, 1)) ?></div>
            <a href="<?= SITE_URL ?>/dashboard.php" style="font-size:14px;font-weight:500;color:var(--gray-700);text-decoration:none;"><?= htmlspecialchars($_SESSION['user_firstname'] ?? '') ?></a>
            <a href="<?= SITE_URL ?>/logout.php" class="btn btn-sm" style="color:var(--gray-500);border:1px solid var(--gray-200);border-radius:6px;padding:4px 12px;font-size:12px;">Log Out</a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/login.php" class="btn btn-sm btn-secondary">Sign In</a>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-sm btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<div class="contact-bar">
    <p>📍 <?= COMPANY_ADDRESS ?></p>
    <div class="contacts">
        <span>Globe: <?= COMPANY_GLOBE ?></span>
        <span>Smart: <?= COMPANY_SMART ?></span>
        <span>Sun: <?= COMPANY_SUN ?></span>
    </div>
</div>
