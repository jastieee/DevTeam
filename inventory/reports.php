<?php
$pageTitle  = 'Reports';
$activePage = 'reports';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db = db();

// Date filter
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

// Summary
$totalIn  = $db->prepare('SELECT COALESCE(SUM(qty),0) FROM stock_in  WHERE DATE(received_at) BETWEEN ? AND ?');
$totalIn->execute([$from, $to]);
$totalIn  = $totalIn->fetchColumn();

$totalOut = $db->prepare('SELECT COALESCE(SUM(qty),0) FROM stock_out WHERE DATE(issued_at) BETWEEN ? AND ?');
$totalOut->execute([$from, $to]);
$totalOut = $totalOut->fetchColumn();

$lowStock = $db->query('SELECT * FROM products WHERE qty_on_hand <= reorder_lvl AND reorder_lvl > 0 ORDER BY qty_on_hand ASC')->fetchAll();

// Top received products
$topIn = $db->prepare(
    'SELECT p.name, p.sku, SUM(si.qty) as total
     FROM stock_in si JOIN products p ON p.id = si.product_id
     WHERE DATE(si.received_at) BETWEEN ? AND ?
     GROUP BY si.product_id ORDER BY total DESC LIMIT 10'
);
$topIn->execute([$from, $to]);
$topIn = $topIn->fetchAll();

// Top issued products
$topOut = $db->prepare(
    'SELECT p.name, p.sku, SUM(so.qty) as total
     FROM stock_out so JOIN products p ON p.id = so.product_id
     WHERE DATE(so.issued_at) BETWEEN ? AND ?
     GROUP BY so.product_id ORDER BY total DESC LIMIT 10'
);
$topOut->execute([$from, $to]);
$topOut = $topOut->fetchAll();

// Asset summary by status
$assetStatus = $db->query(
    'SELECT status, COUNT(*) as cnt FROM assets GROUP BY status'
)->fetchAll();
?>

<div class="page-header">
    <h2>Reports</h2>
</div>

<!-- Date filter -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-toolbar">
        <form method="GET" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
            <label style="font-size:.78rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)">From</label>
            <input type="date" name="from" value="<?= $from ?>" style="padding:.45rem .75rem;border:1.5px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.85rem;background:var(--subtle);color:var(--ink)">
            <label style="font-size:.78rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)">To</label>
            <input type="date" name="to" value="<?= $to ?>" style="padding:.45rem .75rem;border:1.5px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.85rem;background:var(--subtle);color:var(--ink)">
            <button class="btn btn-primary btn-sm" type="submit">Apply</button>
            <a href="reports.php" class="btn btn-outline btn-sm">Reset</a>
        </form>
    </div>
</div>

<!-- Summary stats -->
<div class="stat-grid" style="margin-bottom:1.5rem">
    <div class="stat-card green">
        <span class="stat-label">Total Received</span>
        <span class="stat-value"><?= number_format($totalIn, 2) ?></span>
        <span class="stat-sub">Units received in period</span>
    </div>
    <div class="stat-card accent">
        <span class="stat-label">Total Issued</span>
        <span class="stat-value"><?= number_format($totalOut, 2) ?></span>
        <span class="stat-sub">Units issued in period</span>
    </div>
    <div class="stat-card amber">
        <span class="stat-label">Low Stock Items</span>
        <span class="stat-value"><?= count($lowStock) ?></span>
        <span class="stat-sub">Below reorder level</span>
    </div>
    <div class="stat-card blue">
        <span class="stat-label">Net Movement</span>
        <span class="stat-value"><?= number_format($totalIn - $totalOut, 2) ?></span>
        <span class="stat-sub">In minus out</span>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem">
    <!-- Top received -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem">Top Received Products</strong>
        </div>
        <table>
            <thead><tr><th>SKU</th><th>Product</th><th>Total In</th></tr></thead>
            <tbody>
            <?php if ($topIn): foreach ($topIn as $r): ?>
            <tr>
                <td data-label="SKU"><code style="font-size:.78rem"><?= htmlspecialchars($r['sku']) ?></code></td>
                <td data-label="Product"><?= htmlspecialchars($r['name']) ?></td>
                <td data-label="Total In"><span class="badge badge-green">+<?= number_format($r['total'],2) ?></span></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" class="table-empty">No data for this period.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Top issued -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem">Top Issued Products</strong>
        </div>
        <table>
            <thead><tr><th>SKU</th><th>Product</th><th>Total Out</th></tr></thead>
            <tbody>
            <?php if ($topOut): foreach ($topOut as $r): ?>
            <tr>
                <td data-label="SKU"><code style="font-size:.78rem"><?= htmlspecialchars($r['sku']) ?></code></td>
                <td data-label="Product"><?= htmlspecialchars($r['name']) ?></td>
                <td data-label="Total Out"><span class="badge badge-red">-<?= number_format($r['total'],2) ?></span></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" class="table-empty">No data for this period.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Low stock -->
<?php if ($lowStock): ?>
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-toolbar">
        <strong style="font-size:.9rem;color:var(--red)">⚠ Low Stock Items</strong>
    </div>
    <table>
        <thead><tr>
            <th>SKU</th><th>Product</th><th>On Hand</th><th>Reorder Level</th><th>Shortage</th>
        </tr></thead>
        <tbody>
        <?php foreach ($lowStock as $item): ?>
        <tr class="low-stock">
            <td data-label="SKU"><code style="font-size:.78rem"><?= htmlspecialchars($item['sku']) ?></code></td>
            <td data-label="Product"><?= htmlspecialchars($item['name']) ?></td>
            <td data-label="On Hand"><span class="badge badge-red"><?= number_format($item['qty_on_hand'],2) ?></span></td>
            <td data-label="Reorder Lvl"><?= number_format($item['reorder_lvl'],2) ?></td>
            <td data-label="Shortage"><span class="badge badge-amber"><?= number_format($item['reorder_lvl'] - $item['qty_on_hand'],2) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Asset status breakdown -->
<div class="table-wrap">
    <div class="table-toolbar">
        <strong style="font-size:.9rem">Asset Status Breakdown</strong>
    </div>
    <table>
        <thead><tr><th>Status</th><th>Count</th></tr></thead>
        <tbody>
        <?php
        $statusColors = ['active'=>'badge-green','under_repair'=>'badge-amber','retired'=>'badge-gray','disposed'=>'badge-red'];
        if ($assetStatus): foreach ($assetStatus as $s): ?>
        <tr>
            <td data-label="Status">
                <span class="badge <?= $statusColors[$s['status']] ?? 'badge-gray' ?>">
                    <?= str_replace('_',' ', ucfirst($s['status'])) ?>
                </span>
            </td>
            <td data-label="Count"><strong><?= $s['cnt'] ?></strong></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="2" class="table-empty">No assets yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>