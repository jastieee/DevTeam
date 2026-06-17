<?php
define('API_TOKEN', '27a94d2ed0cac93a1d9a34f6edbeae14542e49f1884795b90567eaa45cb8a687');

// ─────────────────────────────
// JOB SYSTEM (QUEUE DB)
// ─────────────────────────────
define('JOB_DB_HOST', 'localhost');
define('JOB_DB_NAME', 'newtont9_devteam');
define('JOB_DB_USER', 'newtont9_dev');
define('JOB_DB_PASS', 'Newton@2026!');

// ─────────────────────────────
// CPANEL (FOR DB CREATION)
// ─────────────────────────────
define('CPANEL_USER', 'newtont9');
define('CPANEL_TOKEN', 'M0WRUIG4RGP3L0P0F44XWTNPKVCI3VB8');
define('CPANEL_HOST', 'localhost');

// ─────────────────────────────
// APP DB USER (FOR RUN_SQL)
// ─────────────────────────────
define('APP_DB_USER', 'newtont9_3toys');
define('APP_DB_PASS', 'nssi@2026');

function job_db() {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO(
            "mysql:host=" . JOB_DB_HOST . ";dbname=" . JOB_DB_NAME,
            JOB_DB_USER,
            JOB_DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}

function app_db($db) {
    $fullDb = CPANEL_USER . '_' . $db;

    return new PDO(
        "mysql:host=localhost;dbname=$fullDb;charset=utf8",
        APP_DB_USER,
        APP_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}
// function db() {
//     static $pdo;
//     if (!$pdo) {
//         $pdo = new PDO(
//             'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
//             DB_USER, DB_PASS,
//             [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
//         );
//     }
//     return $pdo;
// }

function cpanel_api2($module, $function, $params = []) {
    $query = http_build_query(array_merge([
        'cpanel_jsonapi_user'    => CPANEL_USER,
        'cpanel_jsonapi_module'  => $module,
        'cpanel_jsonapi_func'    => $function,
        'cpanel_jsonapi_apiversion' => '2',
    ], $params));

    $url = "http://" . CPANEL_HOST . ":2082/json-api/cpanel?" . $query;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: cpanel ' . CPANEL_USER . ':' . CPANEL_TOKEN
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) throw new Exception("cURL error: $err");

    $data = json_decode($response, true);
    if (!$data) throw new Exception("Invalid cPanel response");

    $result = $data['cpanelresult'] ?? [];
    if (isset($result['error'])) {
        throw new Exception("cPanel API2 error: " . $result['error']);
    }

    return $result['data'] ?? [];
}

function cpanel_uapi($module, $function, $params = []) {
    $query = http_build_query($params);
    $url = "http://" . CPANEL_HOST . ":2082/execute/{$module}/{$function}?" . $query;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: cpanel ' . CPANEL_USER . ':' . CPANEL_TOKEN
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) throw new Exception("cURL error: $err");

    $data = json_decode($response, true);
    if (!$data) throw new Exception("Invalid cPanel response");
    if (($data['status'] ?? 0) !== 1) {
        $msg = implode(', ', $data['errors'] ?? ['Unknown cPanel error']);
        throw new Exception("cPanel error: $msg");
    }

    return $data['data'] ?? [];
}