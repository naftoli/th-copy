<?php
require_once __DIR__ . '/../../includes/globals.php';

header('Content-Type: application/json');

// Sanitize job ID — only allow safe characters
$jobId = isset($_GET['jobId']) ? preg_replace('/[^a-zA-Z0-9_.]/', '', $_GET['jobId']) : '';

if (!$jobId) {
    http_response_code(400);
    echo json_encode(array('error' => 'jobId is required'));
    exit;
}

try {
    $redis = new Redis();
    $host = !empty($GLOBALS['global_redis_host']) ? $GLOBALS['global_redis_host'] : '127.0.0.1';
    $port = !empty($GLOBALS['global_redis_port']) ? (int) $GLOBALS['global_redis_port'] : 6379;
    $redis->connect($host, $port, 1.5);
    if (!empty($GLOBALS['global_redis_password'])) {
        $redis->auth($GLOBALS['global_redis_password']);
    }
    if (isset($GLOBALS['global_redis_db'])) {
        $redis->select((int) $GLOBALS['global_redis_db']);
    }

    $result = $redis->get('pdf_status:' . $jobId);

    if (!$result) {
        http_response_code(404);
        echo json_encode(array('error' => 'Job not found'));
        exit;
    }

    echo $result;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Status lookup error: ' . $e->getMessage()));
}