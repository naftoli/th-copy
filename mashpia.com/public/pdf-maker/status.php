<?php
require __DIR__ . '/vendor/autoload.php';

use Predis\Client as Redis;

header('Content-Type: application/json');

// Sanitize job ID — only allow safe characters
$jobId = isset($_GET['jobId']) ? preg_replace('/[^a-zA-Z0-9_.]/', '', $_GET['jobId']) : '';

if (!$jobId) {
    http_response_code(400);
    echo json_encode(array('error' => 'jobId is required'));
    exit;
}

try {
    $redis  = new Redis();
    $result = $redis->get('pdf_status:' . $jobId);

    if (!$result) {
        http_response_code(404);
        echo json_encode(array('error' => 'Job not found'));
        exit;
    }

    echo $result;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Status lookup error: ' . $e->getMessage()));
}