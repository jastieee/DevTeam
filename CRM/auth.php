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
    return $_SESSION['crm_user'] ?? null;  // ← crm_user (not inv_user)
}

function require_auth(): void {
    if (!auth_user()) {
        header('Location: login.php');
        exit;
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['crm_user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
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

    db()->prepare('DELETE FROM crm_otp_codes WHERE email = ?')->execute([$email]);
    db()->prepare('INSERT INTO crm_otp_codes (email, code, expires_at) VALUES (?, ?, NOW() + INTERVAL 15 MINUTE)')
       ->execute([$email, $code]);

    $subject = '[Newton CRM] Your OTP Verification Code';
    $body    = "Hi $name,\n\nYour OTP code is: $code\n\nThis code expires in 15 minutes.\n\nDo not share this with anyone.\n\n— Newton CRM Team";
    $headers = "From: noreply@newtonscanning.com.ph\r\n"
             . "Reply-To: program@newton.com.ph\r\n"
             . "X-Mailer: PHP/" . phpversion();

    mail($email, $subject, $body, $headers);
    return $code;
}

function verify_otp(string $email, string $code): bool {
    $stmt = db()->prepare(
        'SELECT id FROM crm_otp_codes
         WHERE email = ? AND code = ? AND used = 0 AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([$email, $code]);
    $row = $stmt->fetch();
    if (!$row) return false;

    db()->prepare('UPDATE crm_otp_codes SET used = 1 WHERE id = ?')
       ->execute([$row['id']]);
    return true;
}