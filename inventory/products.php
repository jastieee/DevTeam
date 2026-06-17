<?php
$pageTitle  = 'Products / SKUs';
$activePage = 'products';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db  = db();
$msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $sku    = trim($_POST['sku']);
        $name   = trim($_POST['name']);
        $cat    = $_POST['category_id'] ?: null;
        $unit   = trim($_POST['unit']);
        $qty    = (float)$_POST['qty_on_hand'];
        $reord  = (float)$_POST['reorder_lvl'];
        $desc   = trim($_POST['description']);

        if ($action === 'add') {
            $db->prepare('INSERT INTO products (sku,name,category_id,unit,qty_on_hand,reorder_lvl,description) VALUES (?,?,?,?,?,?,?)')
               ->execute([$sku,$name,$cat,$unit,$qty,$reord,$desc]);
            $msg = 'Product added successfully.';
        } else {
            $db->prepare('UPDATE products SET sku=?,name=?,category_id=?,unit=?,qty_on_hand=?,reorder_lvl=?,description=? WHERE id=?')
               ->execute([$sku,$name,$cat,$unit,$qty,$reord,$desc,(int)$_POST['id']]);
            $msg = 'Product updated.';
        }
    }

    if ($action === 'delete') {
        $db->prepare('DELETE FROM products WHERE id=?')->execute([(int)$_POST['id']]);
        $msg = 'Product deleted.';
    }
}

$search = trim($_GET['q'] ?? '');
$sql    = 'SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id';
$params = [];
if ($search) {
    $sql    .= ' WHERE p.name LIKE ? OR p.sku LIKE ?';
    $params  = ["%$search%", "%$search%"];
}
$sql .= ' ORDER BY p.name';
$products = $db->prepare($sql);
$products->execute($params);
$products = $products->fetchAll();

$categories = $db->query("SELECT * FROM categories WHERE type IN ('product','both') ORDER BY name")->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-success" style="margin-bottom:1.25rem"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="page-header">
    <h2>Products / SKUs</h2>
    <div class="page-header-actions">
        <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Product</button>
    </div>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <form method="GET" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
            <input class="search-input" type="text" name="q" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline btn-sm" type="submit">Search</button>
            <?php if ($search): ?><a href="products.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
        </form>
        <span style="font-size:.8rem;color:var(--muted)"><?= count($products) ?> items</span>
    </div>
    <table>
        <thead><tr>
            <th>SKU</th><th>Name</th><th>Category</th><th>Unit</th>
            <th>On Hand</th><th>Reorder Lvl</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php if ($products): foreach ($products as $p):
            $low = $p['reorder_lvl'] > 0 && $p['qty_on_hand'] <= $p['reorder_lvl'];
        ?>
        <tr class="<?= $low ? 'low-stock' : '' ?>">
            <td data-label="SKU"><code style="font-size:.8rem"><?= htmlspecialchars($p['sku']) ?></code></td>
            <td data-label="Name"><?= htmlspecialchars($p['name']) ?></td>
            <td data-label="Category"><?= htmlspecialchars($p['cat_name'] ?? '—') ?></td>
            <td data-label="Unit"><?= htmlspecialchars($p['unit']) ?></td>
            <td data-label="On Hand"><strong><?= number_format($p['qty_on_hand'],2) ?></strong></td>
            <td data-label="Reorder Lvl"><?= number_format($p['reorder_lvl'],2) ?></td>
            <td data-label="Status">
                <?php if ($low): ?>
                <span class="badge badge-red">Low Stock</span>
                <?php else: ?>
                <span class="badge badge-green">OK</span>
                <?php endif; ?>
            </td>
            <td data-label="Actions" style="white-space:nowrap">
                <button class="btn btn-outline btn-sm"
                    onclick="openEdit(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Del</button>
                </form>
            </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="table-empty">No products found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal-backdrop" id="addModal">
<div class="modal">
    <div class="modal-header">
        <h3>Add Product</h3>
        <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="add">
    <div class="modal-body">
        <div class="form-row">
            <div class="form-group">
                <label>SKU *</label>
                <input type="text" name="sku" required placeholder="e.g. BRC-001">
            </div>
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" value="pcs" placeholder="pcs / box / set">
            </div>
        </div>
        <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" required placeholder="Full product name">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id">
                <option value="">— None —</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Qty on Hand</label>
                <input type="number" name="qty_on_hand" value="0" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label>Reorder Level</label>
                <input type="number" name="reorder_lvl" value="0" step="0.01" min="0">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Optional notes..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </div>
    </form>
</div>
</div>

<!-- Edit Modal -->
<div class="modal-backdrop" id="editModal">
<div class="modal">
    <div class="modal-header">
        <h3>Edit Product</h3>
        <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" id="edit_id">
    <div class="modal-body">
        <div class="form-row">
            <div class="form-group">
                <label>SKU *</label>
                <input type="text" name="sku" id="edit_sku" required>
            </div>
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" id="edit_unit">
            </div>
        </div>
        <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" id="edit_name" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" id="edit_cat">
                <option value="">— None —</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Qty on Hand</label>
                <input type="number" name="qty_on_hand" id="edit_qty" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label>Reorder Level</label>
                <input type="number" name="reorder_lvl" id="edit_reorder" step="0.01" min="0">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="edit_desc"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
    </form>
</div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openEdit(p) {
    document.getElementById('edit_id').value     = p.id;
    document.getElementById('edit_sku').value    = p.sku;
    document.getElementById('edit_name').value   = p.name;
    document.getElementById('edit_unit').value   = p.unit;
    document.getElementById('edit_qty').value    = p.qty_on_hand;
    document.getElementById('edit_reorder').value= p.reorder_lvl;
    document.getElementById('edit_desc').value   = p.description || '';
    document.getElementById('edit_cat').value    = p.category_id || '';
    openModal('editModal');
}
document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
});
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>