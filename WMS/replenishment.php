<?php
$pageTitle  = 'Storage Reallocation';
$activePage = 'replenishment';
require_once __DIR__ . '/layout.php';
?>

<div class="page-head">
    <div>
        <h2 style="font-family:'DM Serif Display', serif; font-size:1.4rem; margin-bottom:.25rem;">Storage Reallocation</h2>
        <p style="color:var(--muted); font-size:.9rem; max-width:560px;">Move stock between locations to rebalance picking faces and keep fast-moving SKUs accessible.</p>
    </div>
</div>

<div class="empty-state">
    <span class="empty-state-icon">🚧</span>
    <h3>This step is coming up next</h3>
    <p>We're building this part of the warehouse cycle right after the login flow is finalized.</p>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
