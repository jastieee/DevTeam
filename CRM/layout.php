<?php
// layout.php — include at top of every CRM page
// Usage: set $pageTitle and $activePage before including
require_once __DIR__ . '/auth.php';
require_auth();
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'CRM') ?> — Newton CRM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="crm.css">
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <span class="logo-dot"></span>
        <span class="logo-name">Newton</span>
        <span class="logo-sub">CRM</span>
        <span class="phase-tag">Phase 1</span>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-group-label">Overview</p>
        <a href="dashboard.php" class="nav-item <?= ($activePage==='dashboard') ? 'active' : '' ?>">
            <span class="nav-icon">▣</span> Dashboard
        </a>

        <p class="nav-group-label">CRM</p>
        <a href="customers.php" class="nav-item <?= ($activePage==='customers') ? 'active' : '' ?>">
            <span class="nav-icon">◎</span> Customers
        </a>
        <a href="leads.php" class="nav-item <?= ($activePage==='leads') ? 'active' : '' ?>">
            <span class="nav-icon">◈</span> Leads
        </a>
        <a href="pipeline.php" class="nav-item <?= ($activePage==='pipeline') ? 'active' : '' ?>">
            <span class="nav-icon">⇢</span> Sales Pipeline
        </a>
        <a href="activities.php" class="nav-item <?= ($activePage==='activities') ? 'active' : '' ?>">
            <span class="nav-icon">◉</span> Activities
        </a>
        <a href="quotations.php" class="nav-item <?= ($activePage==='quotations') ? 'active' : '' ?>">
            <span class="nav-icon">≡</span> Quotations
        </a>

        <?php if ($user['role'] === 'admin' || $user['role'] === 'manager'): ?>
        <p class="nav-group-label">Management</p>
        <a href="users.php" class="nav-item <?= ($activePage==='users') ? 'active' : '' ?>">
            <span class="nav-icon">⊕</span> User Accounts
        </a>
        <?php endif; ?>
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