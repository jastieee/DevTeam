<?php

require_once __DIR__ . '/config.php';

echo "Worker started...\n";

while (true) {

    // ── Reset jobs stuck in 'running' for more than 5 minutes ──
    job_db()->exec("
        UPDATE jobs
        SET status = 'pending', result = 'Reset after timeout', updated_at = NOW()
        WHERE status = 'running'
          AND updated_at < NOW() - INTERVAL 5 MINUTE
    ");

    $jobs = job_db()
        ->query("SELECT * FROM jobs WHERE status='pending' ORDER BY id ASC LIMIT 10")
        ->fetchAll(PDO::FETCH_ASSOC);

    if (!$jobs) {
        echo "No jobs... sleeping 5 seconds\n";
        sleep(5);
        continue;
    }

    foreach ($jobs as $job) {

        echo "Processing Job #{$job['id']} — {$job['command']}\n";

        job_db()->prepare("UPDATE jobs SET status='running', updated_at=NOW() WHERE id=?")
            ->execute([$job['id']]);

        $status = 'done';
        $result = '';

        try {
            $payload = json_decode($job['payload'], true) ?? [];

            switch ($job['command']) {

                // ─── CREATE DATABASE ─────────────────────────────────────
                case 'create_database':
                    $dbname = $payload['name'] ?? '';
                    if (!$dbname) throw new Exception("Missing: name");

                    cpanel_uapi('Mysql', 'create_database', [
                        'name' => CPANEL_USER . '_' . $dbname
                    ]);
                    $result = "Database created: " . CPANEL_USER . "_" . $dbname;
                    break;

                // ─── CREATE DATABASE USER ────────────────────────────────
                case 'create_db_user':
                    $username = $payload['username'] ?? '';
                    $password = $payload['password'] ?? '';
                    if (!$username || !$password) throw new Exception("Missing: username, password");

                    cpanel_uapi('Mysql', 'create_user', [
                        'name'     => CPANEL_USER . '_' . $username,
                        'password' => $password
                    ]);
                    $result = "DB user created: " . CPANEL_USER . "_" . $username;
                    break;

                // ─── GRANT USER ACCESS TO DATABASE ───────────────────────
                case 'grant_db_access':
                    $dbname   = $payload['database'] ?? '';
                    $username = $payload['username'] ?? '';
                    $privs    = $payload['privileges'] ?? 'ALL PRIVILEGES';
                    if (!$dbname || !$username) throw new Exception("Missing: database, username");

                    cpanel_uapi('Mysql', 'set_privileges_on_database', [
                        'database'   => CPANEL_USER . '_' . $dbname,
                        'user'       => CPANEL_USER . '_' . $username,
                        'privileges' => $privs
                    ]);
                    $result = "Granted $privs to " . CPANEL_USER . "_$username on " . CPANEL_USER . "_$dbname";
                    break;

               // ─── ADD CRON JOB ─────────────────────────────────────────
                case 'add_cronjob':
                    $minute  = $payload['minute']  ?? '0';
                    $hour    = $payload['hour']    ?? '0';
                    $day     = $payload['day']     ?? '*';
                    $month   = $payload['month']   ?? '*';
                    $weekday = $payload['weekday'] ?? '*';
                    $command = $payload['command'] ?? '';
                    if (!$command) throw new Exception("Missing: command");

                    cpanel_api2('Cron', 'add_line', [
                        'minute'  => $minute,
                        'hour'    => $hour,
                        'day'     => $day,
                        'month'   => $month,
                        'weekday' => $weekday,
                        'command' => $command
                    ]);
                    $result = "Cron job added: $minute $hour $day $month $weekday — $command";
                    break;

                // ─── LIST CRON JOBS ───────────────────────────────────────
                case 'list_cronjobs':
                    $data = cpanel_api2('Cron', 'list_lines');
                    $result = json_encode($data);
                    break;

                // ─── LIST DATABASES ───────────────────────────────────────
                case 'list_databases':
                    $data = cpanel_uapi('Mysql', 'list_databases');
                    $result = json_encode($data);
                    break;

                // ─── PING ─────────────────────────────────────────────────
                case 'ping':
                    $result = "pong — " . date('Y-m-d H:i:s');
                    break;

                // ─── RUN SQL ──────────────────────────────────────────────
                case 'run_sql':
                    $db    = $payload['database'] ?? '';
                    $query = $payload['query']    ?? '';
                    if (!$db || !$query) throw new Exception("Missing: database or query");

                    $pdo = app_db($db);
                    $pdo->exec($query);
                    $result = "SQL executed successfully on " . CPANEL_USER . "_" . $db;
                    break;

                    // ─── UPLOAD / WRITE FILE ─────────────────────────────────────
                case 'write_file':
                    $path    = $payload['path']    ?? '';
                    $content = $payload['content'] ?? '';
                    if (!$path || !$content === false) throw new Exception("Missing: path or content");

                    // Optional: restrict to safe directories
                    $allowed_base = '/home1/' . CPANEL_USER . '/';
                    if (!str_starts_with(realpath(dirname($path)) . '/', $allowed_base)) {
                        throw new Exception("Path not allowed");
                    }

                    file_put_contents($path, $content);
                    $result = "File written: $path";
                    break;

                default:
                    $status = 'failed';
                    $result = "Unknown command: {$job['command']}";
            }

        } catch (Exception $e) {
            $status = 'failed';
            $result = $e->getMessage();
            echo "  ERROR: $result\n";
        }

        job_db()->prepare("UPDATE jobs SET status=?, result=?, updated_at=NOW() WHERE id=?")
            ->execute([$status, $result, $job['id']]);

        echo "  Done — status: $status\n";
    }

    sleep(5);
}