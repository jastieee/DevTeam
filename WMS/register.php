<?php
require_once __DIR__ . '/auth.php';
if (auth_user()) { header('Location: index.php'); exit; }

$error = $success = '';
$step  = $_SESSION['otp_step'] ?? 1;

// Reset session if ?reset=1
if (isset($_GET['reset'])) {
    unset($_SESSION['pending_email'], $_SESSION['pending_name'],
          $_SESSION['pending_pass'], $_SESSION['otp_step']);
    header('Location: register.php'); exit;
}

// ── STEP 2: Verify OTP ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $email = $_SESSION['pending_email'] ?? trim($_POST['reg_email'] ?? '');
    $name  = $_SESSION['pending_name']  ?? trim($_POST['reg_name']  ?? '');
    $pass  = $_SESSION['pending_pass']  ?? trim($_POST['reg_pass']  ?? '');
    $code  = trim($_POST['otp']);

    if (verify_otp($email, $code)) {
        $hash      = password_hash($pass, PASSWORD_DEFAULT);
        $createdAt = date('Y-m-d H:i:s');

        db()->prepare('
            INSERT INTO users (name, email, password, email_verified, created_at)
            VALUES (?, ?, ?, 1, ?)
        ')->execute([$name, $email, $hash, $createdAt]);

        unset($_SESSION['pending_email'], $_SESSION['pending_name'],
              $_SESSION['pending_pass'], $_SESSION['otp_step']);

        $success = 'Account verified! You can now sign in.';
        $step    = 1;
    } else {
        $error = 'Invalid or expired OTP. Please try again.';
        $step  = 2;
    }
}

// ── STEP 1: Submit registration form ──
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = trim($_POST['password']);
    $pass2 = trim($_POST['confirm']);

    if (!$name || !$email || !$pass || !$pass2) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        $check = db()->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $_SESSION['pending_name']  = $name;
            $_SESSION['pending_email'] = $email;
            $_SESSION['pending_pass']  = $pass;
            $_SESSION['otp_step']      = 2;
            $step = 2;
            send_otp($email, $name);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — Newton WMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="wms.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="auth-logo">
        <span class="logo-dot"></span>
        <span>Newton</span>
        <small>Warehouse Management System</small>
    </div>

    <?php if ($step === 2): ?>
        <h2>Check your email</h2>
        <p class="auth-sub">We sent a 6-digit code to <strong><?= htmlspecialchars($_SESSION['pending_email'] ?? '') ?></strong>. Enter it below to verify your account.</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="reg_email" value="<?= htmlspecialchars($_SESSION['pending_email'] ?? '') ?>">
            <input type="hidden" name="reg_name"  value="<?= htmlspecialchars($_SESSION['pending_name']  ?? '') ?>">
            <input type="hidden" name="reg_pass"  value="<?= htmlspecialchars($_SESSION['pending_pass']  ?? '') ?>">
            <div class="form-group">
                <label>OTP Code</label>
                <input type="text" name="otp" maxlength="6" placeholder="000000"
                    style="font-size:1.5rem;letter-spacing:.5em;text-align:center;" autofocus required>
            </div>
            <button type="submit" class="btn-submit">Verify →</button>
        </form>
        <p class="auth-switch">
            Wrong email? <a href="register.php?reset=1">Start over</a>
        </p>

    <?php else: ?>
        <h2>Create account</h2>
        <p class="auth-sub">Register to start your <strong>7-day free trial</strong>.</p>

        <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Juan dela Cruz" required autofocus>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@company.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min. 6 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn-submit">Send OTP →</button>
        </form>
        <?php endif; ?>
        <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
    <?php endif; ?>
</div>
</body>
</html>
