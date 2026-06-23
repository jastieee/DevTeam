<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db = db();

/* ── Headline stats ── */
$totalContacts = $db->query('SELECT COUNT(*) FROM crm_contacts')->fetchColumn();
$activeDeals   = $db->query("SELECT COUNT(*) FROM crm_deals WHERE stage NOT IN ('won','lost')")->fetchColumn();
$wonValue      = $db->query("SELECT COALESCE(SUM(value),0) FROM crm_deals WHERE stage = 'won'")->fetchColumn();
$openValue     = $db->query("SELECT COALESCE(SUM(value),0) FROM crm_deals WHERE stage NOT IN ('won','lost')")->fetchColumn();
$newLeads      = $db->query("SELECT COUNT(*) FROM crm_contacts WHERE status = 'lead'")->fetchColumn();
$customers     = $db->query("SELECT COUNT(*) FROM crm_contacts WHERE status = 'customer'")->fetchColumn();

/* ── Pipeline breakdown ── */
$stages = ['new','qualified','proposal','negotiation','won','lost'];
$stageLabels = [
    'new' => 'New', 'qualified' => 'Qualified', 'proposal' => 'Proposal',
    'negotiation' => 'Negotiation', 'won' => 'Won', 'lost' => 'Lost',
];
$pipeline = array_fill_keys($stages, ['count' => 0, 'value' => 0]);
$rows = $db->query('SELECT stage, COUNT(*) AS c, COALESCE(SUM(value),0) AS v FROM crm_deals GROUP BY stage')->fetchAll();
foreach ($rows as $r) {
    if (isset($pipeline[$r['stage']])) {
        $pipeline[$r['stage']] = ['count' => (int)$r['c'], 'value' => (float)$r['v']];
    }
}

/* ── Recent contacts ── */
$recentContacts = $db->query(
    'SELECT c.*, u.name AS owner_name
     FROM crm_contacts c
     LEFT JOIN crm_users u ON u.id = c.assigned_to
     ORDER BY c.created_at DESC LIMIT 5'
)->fetchAll();

/* ── Recent interactions ── */
$recentInteractions = $db->query(
    'SELECT i.*, c.first_name, c.last_name, u.name AS user_name
     FROM crm_interactions i
     JOIN crm_contacts c ON c.id = i.contact_id
     LEFT JOIN crm_users u ON u.id = i.user_id
     ORDER BY i.interacted_at DESC LIMIT 6'
)->fetchAll();

/* ── Recent activity log ── */
$recentActivity = $db->query(
    'SELECT a.*, u.name AS user_name
     FROM crm_activity_logs a
     LEFT JOIN crm_users u ON u.id = a.user_id
     ORDER BY a.created_at DESC LIMIT 6'
)->fetchAll();

$peso = fn($n) => '₱' . number_format((float)$n, 2);

$typeBadge = [
    'call' => 'badge-blue', 'email' => 'badge-cyan', 'meeting' => 'badge-purple',
    'note' => 'badge-gray', 'follow_up' => 'badge-amber',
];
?>

<!-- ── Stat cards ── -->
<div class="stat-grid">
    <div class="stat-card accent">
        <span class="stat-label">Total Contacts</span>
        <span class="stat-value"><?= number_format($totalContacts) ?></span>
        <span class="stat-sub"><?= number_format($newLeads) ?> leads · <?= number_format($customers) ?> customers</span>
    </div>
    <div class="stat-card blue">
        <span class="stat-label">Open Deals</span>
        <span class="stat-value"><?= number_format($activeDeals) ?></span>
        <span class="stat-sub"><?= $peso($openValue) ?> in pipeline</span>
    </div>
    <div class="stat-card green">
        <span class="stat-label">Won Revenue</span>
        <span class="stat-value" style="font-size:1.5rem;"><?= $peso($wonValue) ?></span>
        <span class="stat-sub">Closed deals total</span>
    </div>
    <div class="stat-card purple">
        <span class="stat-label">New Leads</span>
        <span class="stat-value"><?= number_format($newLeads) ?></span>
        <span class="stat-sub">Awaiting qualification</span>
    </div>
</div>

<!-- ── Pipeline overview ── -->
<div class="page-header">
    <h2>Sales Pipeline</h2>
    <div class="page-header-actions">
        <a href="pipeline.php" class="btn btn-outline btn-sm">Open Board</a>
    </div>
</div>
<div class="pipeline-board">
    <?php foreach ($stages as $stage): ?>
    <div class="pipeline-col stage-<?= $stage ?>">
        <div class="pipeline-col-head">
            <span class="pipeline-col-name"><?= $stageLabels[$stage] ?></span>
            <span class="pipeline-col-count"><?= $pipeline[$stage]['count'] ?></span>
        </div>
        <span class="pipeline-col-value"><?= $peso($pipeline[$stage]['value']) ?></span>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Two-column: recent contacts + interactions ── -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.5rem;">

    <!-- Recent Contacts -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Recent Contacts</strong>
            <a href="contacts.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead><tr>
                <th>Name</th><th>Company</th><th>Status</th>
            </tr></thead>
            <tbody>
            <?php if ($recentContacts): foreach ($recentContacts as $c): ?>
            <tr>
                <td data-label="Name"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></td>
                <td data-label="Company" style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($c['company'] ?? '—') ?></td>
                <td data-label="Status">
                    <span class="status-dot <?= htmlspecialchars($c['status']) ?>"></span><?= ucfirst($c['status']) ?>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" class="table-empty">No contacts yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Interactions -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Recent Interactions</strong>
            <a href="interactions.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead><tr>
                <th>Contact</th><th>Type</th><th>When</th>
            </tr></thead>
            <tbody>
            <?php if ($recentInteractions): foreach ($recentInteractions as $i): ?>
            <tr>
                <td data-label="Contact"><?= htmlspecialchars($i['first_name'] . ' ' . $i['last_name']) ?></td>
                <td data-label="Type">
                    <span class="badge <?= $typeBadge[$i['type']] ?? 'badge-gray' ?>"><?= str_replace('_',' ',$i['type']) ?></span>
                </td>
                <td data-label="When" style="font-size:.78rem;color:var(--muted)"><?= date('M j, g:i A', strtotime($i['interacted_at'])) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" class="table-empty">No interactions yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Recent activity log ── -->
<div class="table-wrap">
    <div class="table-toolbar">
        <strong style="font-size:.9rem;">Recent Activity</strong>
        <a href="activity.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <table>
        <thead><tr>
            <th>User</th><th>Action</th><th>Entity</th><th>When</th>
        </tr></thead>
        <tbody>
        <?php if ($recentActivity): foreach ($recentActivity as $a): ?>
        <tr>
            <td data-label="User"><?= htmlspecialchars($a['user_name'] ?? 'System') ?></td>
            <td data-label="Action"><?= htmlspecialchars($a['action']) ?></td>
            <td data-label="Entity" style="font-size:.82rem;color:var(--muted)">
                <?= $a['entity_type'] ? htmlspecialchars($a['entity_type']) . ' #' . (int)$a['entity_id'] : '—' ?>
            </td>
            <td data-label="When" style="font-size:.78rem;color:var(--muted)"><?= date('M j, g:i A', strtotime($a['created_at'])) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4" class="table-empty">No activity recorded yet</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>