<?php
$pageTitle  = 'Assets';
$activePage = 'assets';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db  = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $fields = [
            trim($_POST['asset_code']),
            trim($_POST['name']),
            $_POST['category_id'] ?: null,
            trim($_POST['serial_no']),
            $_POST['status'],
            trim($_POST['assigned_to']),
            trim($_POST['location']),
            $_POST['purchased_at'] ?: null,
            trim($_POST['description']),
        ];
        if ($action === 'add') {
            $db->prepare('INSERT INTO assets (asset_code,name,category_id,serial_no,status,assigned_to,location,purchased_at,description) VALUES (?,?,?,?,?,?,?,?,?)')
               ->execute($fields);
            $msg = 'Asset added.';
        } else {
            $fields[] = (int)$_POST['id'];
            $db->prepare('UPDATE assets SET asset_code=?,name=?,category_id=?,serial_no=?,status=?,assigned_to=?,location=?,purchased_at=?,description=? WHERE id=?')
               ->execute($fields);
            $msg = 'Asset updated.';
        }
    }
    if ($action === 'delete') {
        $db->prepare('DELETE FROM assets WHERE id=?')->execute([(int)$_POST['id']]);
        $msg = 'Asset deleted.';
    }
}

$search = trim($_GET['q'] ?? '');
$sql    = 'SELECT a.*, c.name as cat_name FROM assets a LEFT JOIN categories c ON c.id=a.category_id';
$params = [];
if ($search) {
    $sql   .= ' WHERE a.name LIKE ? OR a.asset_code LIKE ? OR a.serial_no LIKE ?';
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql   .= ' ORDER BY a.name';
$stmt   = $db->prepare($sql);
$stmt->execute($params);
$assets = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM categories WHERE type IN ('asset','both') ORDER BY name")->fetchAll();

$statusColors = [
    'active'       => 'badge-green',
    'under_repair' => 'badge-amber',
    'retired'      => 'badge-gray',
    'disposed'     => 'badge-red',
];
?>

<?php if ($msg): ?>
<div class="alert alert-success" style="margin-bottom:1.25rem"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="page-header">
    <h2>Assets</h2>
    <div class="page-header-actions">
        <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Asset</button>
    </div>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
            <input class="search-input" type="text" name="q" placeholder="Search assets..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline btn-sm" type="submit">Search</button>
            <?php if ($search): ?><a href="assets.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
        </form>
        <span style="font-size:.8rem;color:var(--muted)"><?= count($assets) ?> assets</span>
    </div>
    <table>
        <thead><tr>
            <th>Asset Code</th><th>Name</th><th>Category</th><th>Serial No.</th>
            <th>Status</th><th>Assigned To</th><th>Location</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php if ($assets): foreach ($assets as $a): ?>
        <tr>
            <td data-label="Code"><code style="font-size:.8rem"><?= htmlspecialchars($a['asset_code']) ?></code></td>
            <td data-label="Name"><?= htmlspecialchars($a['name']) ?></td>
            <td data-label="Category"><?= htmlspecialchars($a['cat_name'] ?? '—') ?></td>
            <td data-label="Serial No"><?= htmlspecialchars($a['serial_no'] ?: '—') ?></td>
            <td data-label="Status">
                <span class="badge <?= $statusColors[$a['status']] ?? 'badge-gray' ?>">
                    <?= str_replace('_',' ', ucfirst($a['status'])) ?>
                </span>
            </td>
            <td data-label="Assigned To"><?= htmlspecialchars($a['assigned_to'] ?: '—') ?></td>
            <td data-label="Location"><?= htmlspecialchars($a['location'] ?: '—') ?></td>
            <td data-label="Actions" style="white-space:nowrap">
                <button class="btn btn-outline btn-sm"
                    onclick="openEdit(<?= htmlspecialchars(json_encode($a)) ?>)">Edit</button>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete asset?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button class="btn btn-danger btn-sm">Del</button>
                </form>
            </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="table-empty">No assets found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal-backdrop" id="addModal">
<div class="modal">
    <div class="modal-header">
        <h3>Add Asset</h3>
        <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="add">
    <div class="modal-body">
        <div class="form-row">
            <div class="form-group">
                <label>Asset Code *</label>
                <input type="text" name="asset_code" required placeholder="e.g. RFID-001">
            </div>
            <div class="form-group">
                <label>Serial No.</label>
                <input type="text" name="serial_no" placeholder="Optional">
            </div>
        </div>
        <div class="form-group">
            <label>Asset Name *</label>
            <input type="text" name="name" required placeholder="Full asset name">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">— None —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="under_repair">Under Repair</option>
                    <option value="retired">Retired</option>
                    <option value="disposed">Disposed</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Assigned To</label>
                <input type="text" name="assigned_to" placeholder="Person or dept.">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="Room / floor / site">
            </div>
        </div>
        <div class="form-group">
            <label>Date Purchased</label>
            <input type="date" name="purchased_at">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Optional notes..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Asset</button>
    </div>
    </form>
</div>
</div>

<!-- Edit Modal -->
<div class="modal-backdrop" id="editModal">
<div class="modal">
    <div class="modal-header">
        <h3>Edit Asset</h3>
        <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" id="edit_id">
    <div class="modal-body">
        <div class="form-row">
            <div class="form-group">
                <label>Asset Code *</label>
                <input type="text" name="asset_code" id="edit_code" required>
            </div>
            <div class="form-group">
                <label>Serial No.</label>
                <input type="text" name="serial_no" id="edit_serial">
            </div>
        </div>
        <div class="form-group">
            <label>Asset Name *</label>
            <input type="text" name="name" id="edit_name" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="edit_cat">
                    <option value="">— None —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="edit_status">
                    <option value="active">Active</option>
                    <option value="under_repair">Under Repair</option>
                    <option value="retired">Retired</option>
                    <option value="disposed">Disposed</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Assigned To</label>
                <input type="text" name="assigned_to" id="edit_assigned">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" id="edit_location">
            </div>
        </div>
        <div class="form-group">
            <label>Date Purchased</label>
            <input type="date" name="purchased_at" id="edit_purchased">
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
function openEdit(a) {
    document.getElementById('edit_id').value       = a.id;
    document.getElementById('edit_code').value     = a.asset_code;
    document.getElementById('edit_name').value     = a.name;
    document.getElementById('edit_serial').value   = a.serial_no || '';
    document.getElementById('edit_cat').value      = a.category_id || '';
    document.getElementById('edit_status').value   = a.status;
    document.getElementById('edit_assigned').value = a.assigned_to || '';
    document.getElementById('edit_location').value = a.location || '';
    document.getElementById('edit_purchased').value= a.purchased_at ? a.purchased_at.substring(0,10) : '';
    document.getElementById('edit_desc').value     = a.description || '';
    openModal('editModal');
}
document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
});
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>