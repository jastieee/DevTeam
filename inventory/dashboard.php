<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db = db();

$totalProducts = $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalAssets   = $db->query('SELECT COUNT(*) FROM assets')->fetchColumn();
$lowStock      = $db->query('SELECT COUNT(*) FROM products WHERE qty_on_hand <= reorder_lvl AND reorder_lvl > 0')->fetchColumn();
$activeAssets  = $db->query("SELECT COUNT(*) FROM assets WHERE status = 'active'")->fetchColumn();

$totalIn  = $db->query('SELECT COALESCE(SUM(qty),0) FROM stock_in')->fetchColumn();
$totalOut = $db->query('SELECT COALESCE(SUM(qty),0) FROM stock_out')->fetchColumn();

$recentIn = $db->query(
    'SELECT si.*, p.name as product_name, u.name as user_name
     FROM stock_in si
     JOIN products p ON p.id = si.product_id
     LEFT JOIN users u ON u.id = si.received_by
     ORDER BY si.received_at DESC LIMIT 5'
)->fetchAll();

$recentOut = $db->query(
    'SELECT so.*, p.name as product_name, u.name as user_name
     FROM stock_out so
     JOIN products p ON p.id = so.product_id
     LEFT JOIN users u ON u.id = so.issued_by
     ORDER BY so.issued_at DESC LIMIT 5'
)->fetchAll();

$lowStockItems = $db->query(
    'SELECT * FROM products WHERE qty_on_hand <= reorder_lvl AND reorder_lvl > 0 ORDER BY qty_on_hand ASC LIMIT 5'
)->fetchAll();
?>

<div class="stat-grid">
    <div class="stat-card accent">
        <span class="stat-label">Total Products</span>
        <span class="stat-value"><?= number_format($totalProducts) ?></span>
        <span class="stat-sub">SKUs in system</span>
    </div>
    <div class="stat-card blue">
        <span class="stat-label">Total Assets</span>
        <span class="stat-value"><?= number_format($totalAssets) ?></span>
        <span class="stat-sub"><?= $activeAssets ?> active</span>
    </div>
    <div class="stat-card green">
        <span class="stat-label">Total Stock In</span>
        <span class="stat-value"><?= number_format($totalIn) ?></span>
        <span class="stat-sub">Units received</span>
    </div>
    <div class="stat-card amber">
        <span class="stat-label">Low Stock Items</span>
        <span class="stat-value"><?= number_format($lowStock) ?></span>
        <span class="stat-sub">Below reorder level</span>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.5rem;">

    <!-- Recent Stock In -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Recent Stock In</strong>
            <a href="stock_in.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead><tr>
                <th>Product</th><th>Qty</th><th>Date</th>
            </tr></thead>
            <tbody>
            <?php if ($recentIn): foreach ($recentIn as $r): ?>
            <tr>
                <td data-label="Product"><?= htmlspecialchars($r['product_name']) ?></td>
                <td data-label="Qty"><span class="badge badge-green">+<?= $r['qty'] ?></span></td>
                <td data-label="Date" style="font-size:.78rem;color:var(--muted)"><?= date('M j, Y', strtotime($r['received_at'])) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" class="table-empty">No records yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Stock Out -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Recent Stock Out</strong>
            <a href="stock_out.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead><tr>
                <th>Product</th><th>Qty</th><th>Date</th>
            </tr></thead>
            <tbody>
            <?php if ($recentOut): foreach ($recentOut as $r): ?>
            <tr>
                <td data-label="Product"><?= htmlspecialchars($r['product_name']) ?></td>
                <td data-label="Qty"><span class="badge badge-red">-<?= $r['qty'] ?></span></td>
                <td data-label="Date" style="font-size:.78rem;color:var(--muted)"><?= date('M j, Y', strtotime($r['issued_at'])) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" class="table-empty">No records yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($lowStockItems): ?>
<div class="table-wrap">
    <div class="table-toolbar">
        <strong style="font-size:.9rem; color:var(--red);">⚠ Low Stock Alert</strong>
    </div>
    <table>
        <thead><tr>
            <th>SKU</th><th>Product</th><th>On Hand</th><th>Reorder Level</th>
        </tr></thead>
        <tbody>
        <?php foreach ($lowStockItems as $item): ?>
        <tr class="low-stock">
            <td data-label="SKU"><code><?= htmlspecialchars($item['sku']) ?></code></td>
            <td data-label="Product"><?= htmlspecialchars($item['name']) ?></td>
            <td data-label="On Hand"><span class="badge badge-red"><?= $item['qty_on_hand'] ?></span></td>
            <td data-label="Reorder Lvl"><?= $item['reorder_lvl'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/layout_footer.php'; ?>