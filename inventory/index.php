<?php
require_once __DIR__ . '/auth.php';

// Not logged in → go to login
if (!auth_user()) { header('Location: login.php'); exit; }

$demo_key = 'inventory';
$status   = trial_status($demo_key);

// First time visiting → start trial automatically
if ($status === 'none') {
    start_trial($demo_key);
    $status = 'active';
}

// Trial still active → go to dashboard
if ($status === 'active') {
    header('Location: dashboard.php'); exit;
}

// Trial expired → show expired page (do NOT redirect to dashboard)
$user    = auth_user();
$expires = trial_expires($demo_key);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trial Expired — Newton Inventory</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="inv.css">
</head>
<body class="auth-page">

<div class="auth-card" style="max-width:480px; text-align:center;">
    <div class="auth-logo" style="justify-content:center;">
        <span class="logo-dot"></span>
        <span>Newton</span>
        <small>Inventory System</small>
    </div>

    <div style="font-size:3rem; margin-bottom:1rem;">🔒</div>
    <h2 style="margin-bottom:.5rem;">Your trial has ended</h2>
    <p class="auth-sub" style="margin-bottom:1.5rem;">
        Your 7-day free trial of the <strong>Inventory System</strong> expired on <strong><?= $expires ?></strong>.
        Contact the Newton Dev Team to get full access.
    </p>

    <button class="btn-submit" onclick="document.getElementById('inquireModal').classList.add('open')">
        Inquire Now →
    </button>

    <p style="margin-top:1rem; font-size:.8rem; color:var(--muted);">
        <a href="logout.php" style="color:var(--muted);">Sign out</a>
    </p>
</div>

<!-- Inquire Modal -->
<div class="modal-backdrop" id="inquireModal">
<div class="modal" style="max-width:420px;">
    <div class="modal-header">
        <h3>Get Full Access</h3>
        <button class="modal-close" onclick="document.getElementById('inquireModal').classList.remove('open')">✕</button>
    </div>
    <div class="modal-body" style="display:flex; flex-direction:column; gap:1.25rem;">
        <p style="font-size:.875rem; color:var(--muted); line-height:1.65;">
            Reach out to the Newton Dev Team and we'll set up full access for you.
        </p>
        <div style="display:flex; gap:.75rem; align-items:flex-start;">
            <div class="contact-info-icon">✉️</div>
            <div>
                <strong style="display:block; font-size:.85rem; margin-bottom:.2rem;">Email Us</strong>
                <a href="mailto:program@newton.com.ph" style="font-size:.85rem; color:var(--accent);">program@newton.com.ph</a><br>
                <a href="mailto:irish@newton.com.ph" style="font-size:.85rem; color:var(--accent);">irish@newton.com.ph</a>
            </div>
        </div>
        <div style="display:flex; gap:.75rem; align-items:flex-start;">
            <div class="contact-info-icon">🕐</div>
            <div>
                <strong style="display:block; font-size:.85rem; margin-bottom:.2rem;">Office Hours</strong>
                <span style="font-size:.85rem; color:var(--muted);">Mon – Thu: 8:00 AM – 6:00 PM PHT</span><br>
                <span style="font-size:.85rem; color:var(--muted);">Fri: 8:00 AM – 5:00 PM PHT</span>
            </div>
        </div>
        <div style="display:flex; gap:.75rem; align-items:flex-start;">
            <div class="contact-info-icon">🌐</div>
            <div>
                <strong style="display:block; font-size:.85rem; margin-bottom:.2rem;">Main Website</strong>
                <a href="https://newtonscanning.com" target="_blank" style="font-size:.85rem; color:var(--accent);">newtonscanning.com</a>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-outline" onclick="document.getElementById('inquireModal').classList.remove('open')">Close</button>
        <a href="mailto:program@newton.com.ph?subject=Full Access Request - Inventory System&body=Hi Newton Dev Team,%0A%0AI would like to request full access to the Inventory System demo.%0A%0AName: <?= urlencode($user['name']) ?>%0AEmail: <?= urlencode($user['email']) ?>" class="btn btn-primary">
            Send Email →
        </a>
    </div>
</div>
</div>

<script>
document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
});
</script>

</body>
</html>