<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

echo '<h3>1. DB Connection</h3>';
try {
    $db = db();
    echo '✅ Connected<br>';
} catch (Exception $e) {
    die('❌ DB Error: ' . $e->getMessage());
}

echo '<h3>2. otp_codes table exists</h3>';
try {
    $db->query('SELECT 1 FROM otp_codes LIMIT 1');
    echo '✅ Table exists<br>';
} catch (Exception $e) {
    echo '❌ Table missing — run schema.sql first<br>';
    echo $e->getMessage() . '<br>';
}

echo '<h3>3. users table has email_verified column</h3>';
try {
    $db->query('SELECT email_verified FROM users LIMIT 1');
    echo '✅ Column exists<br>';
} catch (Exception $e) {
    echo '❌ Column missing — run: ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER role<br>';
    echo $e->getMessage() . '<br>';
}

echo '<h3>4. demo_trials table exists</h3>';
try {
    $db->query('SELECT 1 FROM demo_trials LIMIT 1');
    echo '✅ Table exists<br>';
} catch (Exception $e) {
    echo '❌ Table missing — run schema.sql ALTER statements<br>';
    echo $e->getMessage() . '<br>';
}

echo '<h3>5. mail() function test</h3>';
$testMail = mail(
    'program@newton.com.ph',
    'Test from Newton Dev Portal',
    'If you receive this, mail() works.',
    "From: noreply@newtonscanning.com.ph\r\nX-Mailer: PHP/" . phpversion()
);
echo $testMail ? '✅ mail() returned true<br>' : '⚠️ mail() returned false (may still send)<br>';

echo '<h3>6. OTP insert test</h3>';
try {
    $code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $db->prepare('DELETE FROM otp_codes WHERE email = ?')->execute(['test@test.com']);
    $db->prepare('INSERT INTO otp_codes (email, code, expires_at) VALUES (?, ?, ?)')
       ->execute(['test@test.com', $code, $expires]);
    echo '✅ OTP insert works — code would be: ' . $code . '<br>';
    // Clean up
    $db->prepare('DELETE FROM otp_codes WHERE email = ?')->execute(['test@test.com']);
} catch (Exception $e) {
    echo '❌ OTP insert failed: ' . $e->getMessage() . '<br>';
}

echo '<br><strong>All checks done.</strong>';