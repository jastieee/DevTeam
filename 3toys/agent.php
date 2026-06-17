<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// 🔐 TOKEN CHECK
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth, $m)) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

if ($m[1] !== API_TOKEN) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// 📥 READ x-www-form-urlencoded DATA
$command = $_POST['command'] ?? null;
$payload = $_POST['payload'] ?? null;

// convert payload if JSON string
if (is_string($payload)) {
    $payload = json_decode($payload, true);
}

if (!$command) {
    echo json_encode(['error' => 'Command required']);
    exit;
}

// 💾 SAVE TO DB
$stmt = job_db()->prepare("INSERT INTO jobs (command, payload) VALUES (?, ?)");
$stmt->execute([
    $command,
    json_encode($payload)
]);

echo json_encode([
    "status" => true,
    "message" => "Job queued",
    "job_id" => job_db()->lastInsertId()
]);