<?php
// layout.php — include at top of every page
// Usage: $pageTitle, $activePage must be set before including
require_once __DIR__ . '/auth.php';
require_auth();
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Inventory') ?> — Newton Inventory</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="inv.css">
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <span class="logo-dot"></span>
        <span class="logo-name">Newton</span>
        <span class="logo-sub">Inventory</span>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-group-label">Main</p>
        <a href="dashboard.php" class="nav-item <?= ($activePage==='dashboard') ? 'active' : '' ?>">
            <span class="nav-icon">▣</span> Dashboard
        </a>
        <a href="products.php" class="nav-item <?= ($activePage==='products') ? 'active' : '' ?>">
            <span class="nav-icon">◈</span> Products / SKUs
        </a>
        <a href="assets.php" class="nav-item <?= ($activePage==='assets') ? 'active' : '' ?>">
            <span class="nav-icon">◉</span> Assets
        </a>

        <p class="nav-group-label">Movements</p>
        <a href="stock_in.php" class="nav-item <?= ($activePage==='stock_in') ? 'active' : '' ?>">
            <span class="nav-icon">↓</span> Stock In
        </a>
        <a href="stock_out.php" class="nav-item <?= ($activePage==='stock_out') ? 'active' : '' ?>">
            <span class="nav-icon">↑</span> Stock Out
        </a>

        <p class="nav-group-label">Analytics</p>
        <a href="reports.php" class="nav-item <?= ($activePage==='reports') ? 'active' : '' ?>">
            <span class="nav-icon">≡</span> Reports
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name"><?= htmlspecialchars($user['name']) ?></span>
                <span class="sidebar-user-role"><?= ucfirst($user['role']) ?></span>
            </div>
        </div>
        <a href="logout.php" class="logout-btn" title="Logout">⏻</a>
    </div>
</aside>

<!-- ── TOPBAR ── -->
<div class="topbar">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
    <h1 class="topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
    <div class="topbar-right">
        <span class="topbar-date"><?= date('D, M j Y') ?></span>
    </div>
</div>

<!-- ── MAIN ── -->
<main class="main-content">