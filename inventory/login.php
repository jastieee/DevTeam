<?php
require_once __DIR__ . '/auth.php';
if (auth_user()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if ($email && $pass) {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
if ($user && password_verify($pass, $user['password'])) {
    login_user($user);
    session_write_close();
    header('Location: index.php');
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
<title>Login — Newton Inventory</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="inv.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="auth-logo">
        <span class="logo-dot"></span>
        <span>Newton</span>
        <small>Inventory System</small>
    </div>
    <h2>Welcome back</h2>
    <p class="auth-sub">Sign in to your account to continue.</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
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