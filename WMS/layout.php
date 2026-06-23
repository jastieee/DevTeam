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
<title><?= htmlspecialchars($pageTitle ?? 'WMS') ?> — Newton WMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="wms.css">
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <span class="logo-dot"></span>
        <span class="logo-name">Newton</span>
        <span class="logo-sub">WMS</span>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-group-label">Main</p>
        <a href="dashboard.php" class="nav-item <?= ($activePage==='dashboard') ? 'active' : '' ?>">
            <span class="nav-icon">▣</span> Dashboard
        </a>

        <p class="nav-group-label">Inbound</p>
        <a href="receiving.php" class="nav-item <?= ($activePage==='receiving') ? 'active' : '' ?>">
            <span class="nav-icon">↓</span> Receiving
        </a>
        <a href="placement.php" class="nav-item <?= ($activePage==='placement') ? 'active' : '' ?>">
            <span class="nav-icon">◈</span> Classified Placement
        </a>

        <p class="nav-group-label">Warehouse Ops</p>
        <a href="work_documents.php" class="nav-item <?= ($activePage==='work_documents') ? 'active' : '' ?>">
            <span class="nav-icon">▤</span> Work Documents
        </a>
        <a href="replenishment.php" class="nav-item <?= ($activePage==='replenishment') ? 'active' : '' ?>">
            <span class="nav-icon">↻</span> Storage Reallocation
        </a>
        <a href="picking.php" class="nav-item <?= ($activePage==='picking') ? 'active' : '' ?>">
            <span class="nav-icon">⛏</span> Goods Picking
        </a>

        <p class="nav-group-label">Outbound</p>
        <a href="barcode_printing.php" class="nav-item <?= ($activePage==='barcode_printing') ? 'active' : '' ?>">
            <span class="nav-icon">▥</span> Bar Code Printing
        </a>
        <a href="packaging.php" class="nav-item <?= ($activePage==='packaging') ? 'active' : '' ?>">
            <span class="nav-icon">▢</span> Packaging
        </a>
        <a href="dispatching.php" class="nav-item <?= ($activePage==='dispatching') ? 'active' : '' ?>">
            <span class="nav-icon">↑</span> Dispatching
        </a>

        <p class="nav-group-label">Stock</p>
        <a href="counting.php" class="nav-item <?= ($activePage==='counting') ? 'active' : '' ?>">
            <span class="nav-icon">≡</span> Counting / Stocking
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
