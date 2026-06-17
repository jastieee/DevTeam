<?php
require_once __DIR__ . '/config.php';
$sessionPath = __DIR__ . '/sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}

session_save_path($sessionPath);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

function auth_user(): ?array {
    return $_SESSION['inv_user'] ?? null;
}

function require_auth(): void {
    if (!auth_user()) {
        header('Location: login.php');
        exit;
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['inv_user'] = [
        'id'   => $user['id'],
        'name' => $user['name'],
        'email'=> $user['email'],
        'role' => $user['role'],
    ];
    // DO NOT call session_write_close() here — it kills the session before the redirect lands
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: login.php');
    exit;
}

function send_otp(string $email, string $name): string {
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    db()->prepare('DELETE FROM otp_codes WHERE email = ?')->execute([$email]);
    db()->prepare('INSERT INTO otp_codes (email, code, expires_at) VALUES (?, ?, NOW() + INTERVAL 15 MINUTE)')
       ->execute([$email, $code]);

    $subject = '[Newton Dev Portal] Your OTP Verification Code';
    $body    = "Hi $name,\n\nYour OTP code is: $code\n\nThis code expires in 15 minutes.\n\nDo not share this with anyone.\n\n— Newton Dev Team";
    $headers = "From: noreply@newtonscanning.com.ph\r\n"
             . "Reply-To: program@newton.com.ph\r\n"
             . "X-Mailer: PHP/" . phpversion();

    mail($email, $subject, $body, $headers);
    return $code;
}

function verify_otp(string $email, string $code): bool {
    $stmt = db()->prepare(
        'SELECT id FROM otp_codes
         WHERE email = ? AND code = ? AND used = 0 AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([$email, $code]);
    $row = $stmt->fetch();
    if (!$row) return false;

    db()->prepare('UPDATE otp_codes SET used = 1 WHERE id = ?')
       ->execute([$row['id']]);
    return true;
}

function trial_status(string $demo_key): string {
    $user = auth_user();
    if (!$user) return 'none';

    $stmt = db()->prepare(
        'SELECT expires_at FROM demo_trials WHERE user_id = ? AND demo_key = ? LIMIT 1'
    );
    $stmt->execute([$user['id'], $demo_key]);
    $row = $stmt->fetch();

    if (!$row) return 'none';
    return (strtotime($row['expires_at']) > time()) ? 'active' : 'expired';
}

function start_trial(string $demo_key): void {
    $user    = auth_user();
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

    db()->prepare(
        'INSERT IGNORE INTO demo_trials (user_id, demo_key, expires_at) VALUES (?, ?, ?)'
    )->execute([$user['id'], $demo_key, $expires]);
}

function trial_expires(string $demo_key): string {
    $user = auth_user();
    $stmt = db()->prepare(
        'SELECT expires_at FROM demo_trials WHERE user_id = ? AND demo_key = ? LIMIT 1'
    );
    $stmt->execute([$user['id'], $demo_key]);
    $row = $stmt->fetch();
    return $row ? date('F j, Y', strtotime($row['expires_at'])) : '';
}