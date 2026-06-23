<?php
require_once __DIR__ . '/auth.php';
if (auth_user()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if ($email && $pass) {
        $stmt = db()->prepare('SELECT * FROM crm_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password'])) {
            login_user($user);
            session_write_close();
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid email or password.';
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Newton CRM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
/* ── Reset & Variables ── */
*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

:root {
  --ink:      #171717;
  --paper:    #f8f7f4;
  --accent:   #1d4ed8;   /* CRM uses blue instead of inventory's red */
  --muted:    #888480;
  --border:   #e4e0d9;
  --card:     #ffffff;
  --subtle:   #f2f0ec;
}

body {
  font-family: "DM Sans", sans-serif;
  background: var(--paper);
  background-image: radial-gradient(circle, var(--border) 1.5px, transparent 1.5px);
  background-size: 28px 28px;
  color: var(--ink);
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem 1rem;
}

h1, h2, h3 {
  font-family: "DM Serif Display", serif;
}

/* ── Auth Card ── */
.auth-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: 16px;
  padding: 2.5rem 2rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.07);
}

.auth-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.75rem;
}
.auth-logo .logo-dot {
  width: 10px;
  height: 10px;
  background: var(--accent);
  border-radius: 50%;
}
.auth-logo span {
  font-family: "DM Serif Display", serif;
  font-size: 1.2rem;
}
.auth-logo small {
  font-family: "DM Sans", sans-serif;
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--muted);
}

.auth-card h2 {
  font-size: 1.5rem;
  margin-bottom: 0.35rem;
}
.auth-card p.auth-sub {
  font-size: 0.875rem;
  color: var(--muted);
  margin-bottom: 1.75rem;
}
.auth-card .form-group {
  margin-bottom: 1rem;
}
.auth-card label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 0.35rem;
}
.auth-card input {
  width: 100%;
  background: var(--subtle);
  border: 1.5px solid var(--border);
  border-radius: 8px;
  padding: 0.7rem 1rem;
  font-size: 0.9rem;
  font-family: "DM Sans", sans-serif;
  color: var(--ink);
  outline: none;
  transition: border-color 0.2s;
}
.auth-card input:focus {
  border-color: var(--accent);
}
.btn-submit {
  width: 100%;
  background: var(--ink);
  color: var(--paper);
  border: none;
  border-radius: 999px;
  padding: 0.8rem;
  font-size: 0.9rem;
  font-weight: 600;
  font-family: "DM Sans", sans-serif;
  cursor: pointer;
  margin-top: 0.5rem;
  transition: background 0.2s;
}
.btn-submit:hover {
  background: var(--accent);
}

.auth-switch {
  text-align: center;
  font-size: 0.85rem;
  color: var(--muted);
  margin-top: 1.25rem;
}
.auth-switch a {
  color: var(--accent);
  text-decoration: none;
  font-weight: 500;
}
.auth-switch a:hover {
  text-decoration: underline;
}

.alert-error {
  background: #fef2f2;
  border: 1.5px solid #fecaca;
  color: #dc2626;
  border-radius: 8px;
  padding: 0.65rem 1rem;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

/* ── Phase badge ── */
.phase-badge {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  background: #dbeafe;
  color: #1d4ed8;
  border-radius: 999px;
  padding: 0.2rem 0.65rem;
  margin-left: 0.4rem;
  vertical-align: middle;
}
</style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <span class="logo-dot"></span>
        <span>Newton</span>
        <small>CRM</small>
        <span class="phase-badge">Phase 1</span>
    </div>
    <h2>Welcome back</h2>
    <p class="auth-sub">Sign in to your CRM account to continue.</p>

    <?php if ($error): ?>
    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="you@newton.com.ph" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Sign In →</button>
    </form>
    <p class="auth-switch">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>
</body>
</html>