<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/layout.php';

$db = db();

// ── Stat counts ──────────────────────────────────────────────────────────────
$totalContacts  = $db->query("SELECT COUNT(*) FROM crm_contacts")->fetchColumn();
$totalLeads     = $db->query("SELECT COUNT(*) FROM crm_contacts WHERE status = 'lead'")->fetchColumn();
$totalCustomers = $db->query("SELECT COUNT(*) FROM crm_contacts WHERE status = 'customer'")->fetchColumn();
$totalDeals     = $db->query("SELECT COUNT(*) FROM crm_deals")->fetchColumn();
$openDeals      = $db->query("SELECT COUNT(*) FROM crm_deals WHERE stage NOT IN ('won','lost')")->fetchColumn();
$wonDeals       = $db->query("SELECT COUNT(*) FROM crm_deals WHERE stage = 'won'")->fetchColumn();
$lostDeals      = $db->query("SELECT COUNT(*) FROM crm_deals WHERE stage = 'lost'")->fetchColumn();

// Revenue forecast (sum of open deal values)
$forecastValue  = $db->query(
    "SELECT COALESCE(SUM(value),0) FROM crm_deals WHERE stage NOT IN ('won','lost')"
)->fetchColumn();
$wonValue       = $db->query(
    "SELECT COALESCE(SUM(value),0) FROM crm_deals WHERE stage = 'won'"
)->fetchColumn();

// ── Pipeline by stage ────────────────────────────────────────────────────────
$stageOrder = ['new','qualified','proposal','negotiation','won','lost'];
$stageColors = [
    'new'         => '#60a5fa',
    'qualified'   => '#34d399',
    'proposal'    => '#fbbf24',
    'negotiation' => '#f97316',
    'won'         => '#16a34a',
    'lost'        => '#dc2626',
];
$stageLabels = [
    'new'         => 'New',
    'qualified'   => 'Qualified',
    'proposal'    => 'Proposal',
    'negotiation' => 'Negotiation',
    'won'         => 'Won',
    'lost'        => 'Lost',
];

$stageCounts = [];
$stmt = $db->query("SELECT stage, COUNT(*) as cnt FROM crm_deals GROUP BY stage");
foreach ($stmt->fetchAll() as $row) {
    $stageCounts[$row['stage']] = (int)$row['cnt'];
}
foreach ($stageOrder as $s) {
    $stageCounts[$s] = $stageCounts[$s] ?? 0;
}
$maxStageCount = max(array_values($stageCounts)) ?: 1;

// ── Recent leads ─────────────────────────────────────────────────────────────
$recentLeads = $db->query(
    "SELECT c.*, u.name AS assigned_name
     FROM crm_contacts c
     LEFT JOIN crm_users u ON u.id = c.assigned_to
     WHERE c.status IN ('lead','prospect')
     ORDER BY c.created_at DESC
     LIMIT 6"
)->fetchAll();

// ── Recent interactions ──────────────────────────────────────────────────────
$recentActivities = $db->query(
    "SELECT i.*, c.first_name, c.last_name, u.name AS staff_name
     FROM crm_interactions i
     JOIN crm_contacts c ON c.id = i.contact_id
     JOIN crm_users u ON u.id = i.user_id
     ORDER BY i.interacted_at DESC
     LIMIT 8"
)->fetchAll();

// ── Top open deals ───────────────────────────────────────────────────────────
$topDeals = $db->query(
    "SELECT d.*, c.first_name, c.last_name, c.company, u.name AS assigned_name
     FROM crm_deals d
     JOIN crm_contacts c ON c.id = d.contact_id
     LEFT JOIN crm_users u ON u.id = d.assigned_to
     WHERE d.stage NOT IN ('won','lost')
     ORDER BY d.value DESC
     LIMIT 5"
)->fetchAll();

// ── Leads by source ──────────────────────────────────────────────────────────
$leadSources = $db->query(
    "SELECT COALESCE(source,'Unknown') AS src, COUNT(*) AS cnt
     FROM crm_contacts
     GROUP BY src
     ORDER BY cnt DESC
     LIMIT 6"
)->fetchAll();

// ── Win rate ─────────────────────────────────────────────────────────────────
$closedDeals = $wonDeals + $lostDeals;
$winRate     = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100) : 0;

// ── Helpers ──────────────────────────────────────────────────────────────────
function stageClass(string $stage): string {
    return match($stage) {
        'won'         => 'badge-green',
        'lost'        => 'badge-red',
        'proposal'    => 'badge-amber',
        'negotiation' => 'badge-purple',
        'qualified'   => 'badge-blue',
        default       => 'badge-gray',
    };
}
function statusClass(string $status): string {
    return match($status) {
        'customer'   => 'badge-green',
        'prospect'   => 'badge-blue',
        'lead'       => 'badge-amber',
        'inactive'   => 'badge-gray',
        default      => 'badge-gray',
    };
}
function activityIcon(string $type): string {
    return match($type) {
        'call'      => '📞',
        'meeting'   => '🤝',
        'email'     => '✉️',
        'follow_up' => '🔔',
        default     => '📝',
    };
}
function activityDotClass(string $type): string {
    return match($type) {
        'call'      => 'call',
        'meeting'   => 'meeting',
        'email'     => 'email',
        'follow_up' => 'follow',
        default     => 'note',
    };
}
?>

<!-- ── Greeting ─────────────────────────────────────────────────────────── -->
<div style="margin-bottom:2rem;">
    <h2 style="font-size:1.6rem; margin-bottom:.25rem;">
        Good <?= (date('G') < 12) ? 'morning' : ((date('G') < 18) ? 'afternoon' : 'evening') ?>,
        <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋
    </h2>
    <p style="color:var(--muted); font-size:.9rem;">Here's your CRM overview for today.</p>
</div>

<!-- ── KPI Stat Cards ────────────────────────────────────────────────────── -->
<div class="stat-grid">
    <div class="stat-card blue">
        <span class="stat-label">Total Contacts</span>
        <span class="stat-value"><?= number_format($totalContacts) ?></span>
        <span class="stat-sub">All leads &amp; customers</span>
    </div>
    <div class="stat-card amber">
        <span class="stat-label">Active Leads</span>
        <span class="stat-value"><?= number_format($totalLeads) ?></span>
        <span class="stat-sub">In pipeline</span>
    </div>
    <div class="stat-card green">
        <span class="stat-label">Customers</span>
        <span class="stat-value"><?= number_format($totalCustomers) ?></span>
        <span class="stat-sub">Converted accounts</span>
    </div>
    <div class="stat-card accent">
        <span class="stat-label">Open Deals</span>
        <span class="stat-value"><?= number_format($openDeals) ?></span>
        <span class="stat-sub">₱<?= number_format($forecastValue, 0) ?> forecasted</span>
    </div>
    <div class="stat-card purple">
        <span class="stat-label">Win Rate</span>
        <span class="stat-value"><?= $winRate ?>%</span>
        <span class="stat-sub"><?= $wonDeals ?> won / <?= $lostDeals ?> lost</span>
    </div>
    <div class="stat-card green">
        <span class="stat-label">Revenue Won</span>
        <span class="stat-value" style="font-size:1.55rem;">₱<?= number_format($wonValue, 0) ?></span>
        <span class="stat-sub">Closed deals value</span>
    </div>
</div>

<!-- ── Pipeline Overview + Recent Activities ─────────────────────────────── -->
<div class="three-col">

    <!-- Pipeline by Stage -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Sales Pipeline</strong>
            <a href="pipeline.php" class="btn btn-outline btn-sm">View All</a>
        </div>

        <!-- Stacked bar -->
        <div style="padding:1.25rem 1.25rem .5rem;">
            <div class="pipeline-bar">
                <?php foreach ($stageOrder as $s):
                    $cnt   = $stageCounts[$s];
                    $total = array_sum($stageCounts) ?: 1;
                    $pct   = round(($cnt / $total) * 100);
                    if ($pct === 0) continue;
                ?>
                <div class="pipeline-seg"
                     title="<?= $stageLabels[$s] ?>: <?= $cnt ?>"
                     style="flex:<?= $pct ?>; background:<?= $stageColors[$s] ?>"></div>
                <?php endforeach; ?>
            </div>
            <!-- Legend -->
            <div style="display:flex; flex-wrap:wrap; gap:.5rem .9rem; font-size:.72rem; color:var(--muted);">
                <?php foreach ($stageOrder as $s): ?>
                <span>
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?= $stageColors[$s] ?>; margin-right:3px;"></span>
                    <?= $stageLabels[$s] ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stage rows -->
        <div class="stage-list">
            <?php foreach ($stageOrder as $s):
                $cnt = $stageCounts[$s];
                $pct = round(($cnt / $maxStageCount) * 100);
            ?>
            <div class="stage-row">
                <span class="stage-label"><?= $stageLabels[$s] ?></span>
                <div class="stage-track">
                    <div class="stage-fill" style="width:<?= $pct ?>%; background:<?= $stageColors[$s] ?>"></div>
                </div>
                <span class="stage-count"><?= $cnt ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Activity Feed -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Recent Activities</strong>
            <a href="activities.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="activity-feed">
            <?php if ($recentActivities): foreach ($recentActivities as $act): ?>
            <div class="activity-item">
                <div class="activity-dot <?= activityDotClass($act['type']) ?>">
                    <?= activityIcon($act['type']) ?>
                </div>
                <div class="activity-body">
                    <div class="activity-text">
                        <strong><?= htmlspecialchars($act['summary']) ?></strong>
                    </div>
                    <div class="activity-meta">
                        <?= htmlspecialchars($act['first_name'] . ' ' . $act['last_name']) ?>
                        &middot; <?= htmlspecialchars($act['staff_name']) ?>
                        &middot; <?= date('M j, g:i A', strtotime($act['interacted_at'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div style="padding:2.5rem; text-align:center; color:var(--muted); font-size:.875rem;">No activities yet</div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Recent Leads + Top Open Deals ─────────────────────────────────────── -->
<div class="two-col">

    <!-- Recent Leads -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Recent Leads</strong>
            <a href="leads.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead><tr>
                <th>Name</th>
                <th>Company</th>
                <th>Status</th>
                <th>Assigned</th>
            </tr></thead>
            <tbody>
            <?php if ($recentLeads): foreach ($recentLeads as $lead): ?>
            <tr>
                <td data-label="Name">
                    <strong><?= htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']) ?></strong>
                    <?php if ($lead['email']): ?>
                    <div style="font-size:.75rem; color:var(--muted);"><?= htmlspecialchars($lead['email']) ?></div>
                    <?php endif; ?>
                </td>
                <td data-label="Company" style="font-size:.82rem; color:var(--muted);">
                    <?= htmlspecialchars($lead['company'] ?? '—') ?>
                </td>
                <td data-label="Status">
                    <span class="badge <?= statusClass($lead['status']) ?>"><?= ucfirst($lead['status']) ?></span>
                </td>
                <td data-label="Assigned" style="font-size:.82rem; color:var(--muted);">
                    <?= htmlspecialchars($lead['assigned_name'] ?? 'Unassigned') ?>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" class="table-empty">No leads yet — <a href="customers.php" style="color:var(--accent);">add one</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Open Deals -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <strong style="font-size:.9rem;">Top Open Deals</strong>
            <a href="pipeline.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table>
            <thead><tr>
                <th>Deal</th>
                <th>Value</th>
                <th>Stage</th>
            </tr></thead>
            <tbody>
            <?php if ($topDeals): foreach ($topDeals as $deal): ?>
            <tr>
                <td data-label="Deal">
                    <strong><?= htmlspecialchars($deal['title']) ?></strong>
                    <div style="font-size:.75rem; color:var(--muted);">
                        <?= htmlspecialchars($deal['first_name'] . ' ' . $deal['last_name']) ?>
                        <?= $deal['company'] ? '· ' . htmlspecialchars($deal['company']) : '' ?>
                    </div>
                </td>
                <td data-label="Value" style="font-weight:600; white-space:nowrap;">
                    <?= $deal['value'] ? '₱' . number_format($deal['value'], 0) : '—' ?>
                </td>
                <td data-label="Stage">
                    <span class="badge <?= stageClass($deal['stage']) ?>"><?= ucfirst($deal['stage']) ?></span>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" class="table-empty">No open deals yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- ── Lead Sources ───────────────────────────────────────────────────────── -->
<?php if ($leadSources): ?>
<div class="table-wrap" style="margin-bottom:2rem;">
    <div class="table-toolbar">
        <strong style="font-size:.9rem;">Contacts by Source</strong>
    </div>
    <div style="padding:1.25rem; display:flex; flex-wrap:wrap; gap:.75rem;">
        <?php
        $srcColors = ['#60a5fa','#34d399','#fbbf24','#f97316','#a78bfa','#f472b6'];
        $total = array_sum(array_column($leadSources, 'cnt')) ?: 1;
        foreach ($leadSources as $i => $src):
            $pct = round(($src['cnt'] / $total) * 100);
            $color = $srcColors[$i % count($srcColors)];
        ?>
        <div style="display:flex; align-items:center; gap:.5rem; background:var(--subtle); border-radius:999px; padding:.4rem .9rem; font-size:.8rem;">
            <span style="width:8px; height:8px; border-radius:50%; background:<?= $color ?>; flex-shrink:0; display:inline-block;"></span>
            <span style="font-weight:600;"><?= htmlspecialchars($src['src']) ?></span>
            <span style="color:var(--muted);"><?= $src['cnt'] ?> (<?= $pct ?>%)</span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/layout_footer.php'; ?>