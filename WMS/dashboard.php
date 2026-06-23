<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/layout.php';

$cycle = [
    ['label' => 'Receiving',              'icon' => '↓', 'href' => 'receiving.php'],
    ['label' => 'Classified Placement',   'icon' => '◈', 'href' => 'placement.php'],
    ['label' => 'Work Documents',         'icon' => '▤', 'href' => 'work_documents.php'],
    ['label' => 'Storage Reallocation',   'icon' => '↻', 'href' => 'replenishment.php'],
    ['label' => 'Goods Picking',          'icon' => '⛏', 'href' => 'picking.php'],
    ['label' => 'Bar Code Printing',      'icon' => '▥', 'href' => 'barcode_printing.php'],
    ['label' => 'Packaging',              'icon' => '▢', 'href' => 'packaging.php'],
    ['label' => 'Dispatching',            'icon' => '↑', 'href' => 'dispatching.php'],
    ['label' => 'Counting / Stocking',    'icon' => '≡', 'href' => 'counting.php'],
];
?>

<div class="page-head">
    <div>
        <h2 style="font-family:'DM Serif Display', serif; font-size:1.6rem; margin-bottom:.25rem;">
            Welcome, <?= htmlspecialchars($user['name']) ?> 👋
        </h2>
        <p style="color:var(--muted); font-size:.9rem;">
            Here's the warehouse cycle this demo walks through, end to end.
        </p>
    </div>
</div>

<div class="cycle-grid">
    <?php foreach ($cycle as $i => $step): ?>
    <a href="<?= htmlspecialchars($step['href']) ?>" class="cycle-card">
        <span class="cycle-step-num">Step <?= $i + 1 ?></span>
        <span class="cycle-icon"><?= $step['icon'] ?></span>
        <span class="cycle-label"><?= htmlspecialchars($step['label']) ?></span>
    </a>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
