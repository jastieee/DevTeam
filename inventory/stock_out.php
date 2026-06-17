<?php
$pageTitle  = 'Stock Out — Issuing';
$activePage = 'stock_out';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db   = db();
$msg  = '';
$err  = '';
$user = auth_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid  = (int)$_POST['product_id'];
    $qty  = (float)$_POST['qty'];
    $to   = trim($_POST['issued_to']);
    $ref  = trim($_POST['reference']);
    $note = trim($_POST['notes']);

    if ($pid && $qty > 0) {
        // Check available stock
        $onHand = $db->prepare('SELECT qty_on_hand FROM products WHERE id = ?');
        $onHand->execute([$pid]);
        $current = (float)($onHand->fetchColumn() ?? 0);

        if ($qty > $current) {
            $err = "Insufficient stock. Only {$current} units available.";
        } else {
            $db->prepare('INSERT INTO stock_out (product_id,qty,issued_to,reference,notes,issued_by) VALUES (?,?,?,?,?,?)')
               ->execute([$pid, $qty, $to, $ref, $note, $user['id']]);
            $db->prepare('UPDATE products SET qty_on_hand = qty_on_hand - ? WHERE id = ?')
               ->execute([$qty, $pid]);
            $msg = 'Stock issued successfully.';
        }
    } else {
        $err = 'Please select a product and enter a valid quantity.';
    }
}

$products = $db->query('SELECT id, sku, name, qty_on_hand FROM products ORDER BY name')->fetchAll();

$records = $db->query(
    'SELECT so.*, p.name as product_name, p.sku, u.name as user_name
     FROM stock_out so
     JOIN products p ON p.id = so.product_id
     LEFT JOIN users u ON u.id = so.issued_by
     ORDER BY so.issued_at DESC
     LIMIT 100'
)->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-success" style="margin-bottom:1.25rem"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($err): ?>
<div class="alert alert-error" style="margin-bottom:1.25rem"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="page-header">
    <h2>Stock Out — Issuing</h2>
    <button class="btn btn-primary" onclick="openModal('addModal')">+ Issue Stock</button>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <strong style="font-size:.9rem;">Issuance History</strong>
        <span style="font-size:.8rem;color:var(--muted)"><?= count($records) ?> records</span>
    </div>
    <table>
        <thead><tr>
            <th>Date</th><th>SKU</th><th>Product</th><th>Qty Issued</th>
            <th>Issued To</th><th>Reference</th><th>Issued By</th>
        </tr></thead>
        <tbody>
        <?php if ($records): foreach ($records as $r): ?>
        <tr>
            <td data-label="Date" style="font-size:.8rem;white-space:nowrap"><?= date('M j, Y H:i', strtotime($r['issued_at'])) ?></td>
            <td data-label="SKU"><code style="font-size:.78rem"><?= htmlspecialchars($r['sku']) ?></code></td>
            <td data-label="Product"><?= htmlspecialchars($r['product_name']) ?></td>
            <td data-label="Qty"><span class="badge badge-red">-<?= number_format($r['qty'],2) ?></span></td>
            <td data-label="Issued To"><?= htmlspecialchars($r['issued_to'] ?: '—') ?></td>
            <td data-label="Reference"><code style="font-size:.78rem"><?= htmlspecialchars($r['reference'] ?: '—') ?></code></td>
            <td data-label="Issued By" style="font-size:.82rem"><?= htmlspecialchars($r['user_name'] ?? '—') ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="table-empty">No issuance records yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="addModal">
<div class="modal">
    <div class="modal-header">
        <h3>Issue Stock</h3>
        <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
    <div class="modal-body">
        <div class="form-group">
            <label>Product *</label>
            <select name="product_id" required id="productSelect" onchange="updateStock(this)">
                <option value="">— Select product —</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>" data-stock="<?= $p['qty_on_hand'] ?>">
                    [<?= htmlspecialchars($p['sku']) ?>] <?= htmlspecialchars($p['name']) ?> (<?= number_format($p['qty_on_hand'],2) ?> available)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="stockInfo" style="display:none;margin-bottom:1rem;padding:.65rem 1rem;background:var(--subtle);border-radius:8px;font-size:.85rem;color:var(--muted);">
            Available stock: <strong id="stockDisplay">0</strong>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Quantity to Issue *</label>
                <input type="number" name="qty" min="0.01" step="0.01" required placeholder="0" id="qtyInput">
            </div>
            <div class="form-group">
                <label>Reference No.</label>
                <input type="text" name="reference" placeholder="RIS / MR number">
            </div>
        </div>
        <div class="form-group">
            <label>Issued To</label>
            <input type="text" name="issued_to" placeholder="Person or department">
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" placeholder="Optional remarks..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Issue Stock</button>
    </div>
    </form>
</div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function updateStock(sel) {
    const opt   = sel.options[sel.selectedIndex];
    const stock = opt.dataset.stock;
    const info  = document.getElementById('stockInfo');
    if (sel.value) {
        document.getElementById('stockDisplay').textContent = parseFloat(stock).toFixed(2);
        info.style.display = 'block';
        document.getElementById('qtyInput').max = stock;
    } else {
        info.style.display = 'none';
    }
}
document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
});
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>