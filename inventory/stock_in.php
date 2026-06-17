<?php
$pageTitle  = 'Stock In — Receiving';
$activePage = 'stock_in';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db  = db();
$msg = '';
$user = auth_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid  = (int)$_POST['product_id'];
    $qty  = (float)$_POST['qty'];
    $sup  = trim($_POST['supplier']);
    $ref  = trim($_POST['reference']);
    $note = trim($_POST['notes']);

    if ($pid && $qty > 0) {
        $db->prepare('INSERT INTO stock_in (product_id,qty,supplier,reference,notes,received_by) VALUES (?,?,?,?,?,?)')
           ->execute([$pid, $qty, $sup, $ref, $note, $user['id']]);
        // Update qty on hand
        $db->prepare('UPDATE products SET qty_on_hand = qty_on_hand + ? WHERE id = ?')
           ->execute([$qty, $pid]);
        $msg = 'Stock received successfully.';
    } else {
        $msg = 'Please select a product and enter a valid quantity.';
    }
}

$products = $db->query('SELECT id, sku, name FROM products ORDER BY name')->fetchAll();

$records = $db->query(
    'SELECT si.*, p.name as product_name, p.sku, u.name as user_name
     FROM stock_in si
     JOIN products p ON p.id = si.product_id
     LEFT JOIN users u ON u.id = si.received_by
     ORDER BY si.received_at DESC
     LIMIT 100'
)->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-success" style="margin-bottom:1.25rem"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="page-header">
    <h2>Stock In — Receiving</h2>
    <button class="btn btn-green" onclick="openModal('addModal')">+ Receive Stock</button>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <strong style="font-size:.9rem;">Receiving History</strong>
        <span style="font-size:.8rem;color:var(--muted)"><?= count($records) ?> records</span>
    </div>
    <table>
        <thead><tr>
            <th>Date</th><th>SKU</th><th>Product</th><th>Qty Received</th>
            <th>Supplier</th><th>Reference</th><th>Received By</th>
        </tr></thead>
        <tbody>
        <?php if ($records): foreach ($records as $r): ?>
        <tr>
            <td data-label="Date" style="font-size:.8rem;white-space:nowrap"><?= date('M j, Y H:i', strtotime($r['received_at'])) ?></td>
            <td data-label="SKU"><code style="font-size:.78rem"><?= htmlspecialchars($r['sku']) ?></code></td>
            <td data-label="Product"><?= htmlspecialchars($r['product_name']) ?></td>
            <td data-label="Qty"><span class="badge badge-green">+<?= number_format($r['qty'],2) ?></span></td>
            <td data-label="Supplier"><?= htmlspecialchars($r['supplier'] ?: '—') ?></td>
            <td data-label="Reference"><code style="font-size:.78rem"><?= htmlspecialchars($r['reference'] ?: '—') ?></code></td>
            <td data-label="Received By" style="font-size:.82rem"><?= htmlspecialchars($r['user_name'] ?? '—') ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="table-empty">No receiving records yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal-backdrop" id="addModal">
<div class="modal">
    <div class="modal-header">
        <h3>Receive Stock</h3>
        <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
    <div class="modal-body">
        <div class="form-group">
            <label>Product *</label>
            <select name="product_id" required>
                <option value="">— Select product —</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>">[<?= htmlspecialchars($p['sku']) ?>] <?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Quantity Received *</label>
                <input type="number" name="qty" min="0.01" step="0.01" required placeholder="0">
            </div>
            <div class="form-group">
                <label>Reference No.</label>
                <input type="text" name="reference" placeholder="PO / DR number">
            </div>
        </div>
        <div class="form-group">
            <label>Supplier</label>
            <input type="text" name="supplier" placeholder="Supplier name">
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" placeholder="Optional remarks..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-green">Receive Stock</button>
    </div>
    </form>
</div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
});
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>