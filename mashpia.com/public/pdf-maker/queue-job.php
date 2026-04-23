<?php
require_once __DIR__ . '/../../../includes/globals.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['pages']) || !is_array($input['pages'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'pages array is required'));
    exit;
}

$email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
$name  = isset($input['name']) ? strip_tags($input['name']) : 'Recipient';

if (!$email) {
    http_response_code(400);
    echo json_encode(array('error' => 'Invalid email address'));
    exit;
}

// Build pages array
$pages = array();
foreach ($input['pages'] as $html) {
    $pages[] = array('html' => $html);
}

// Generate unique job ID
$jobId = uniqid('pdf_', true);

$job = array(
    'jobId'    => $jobId,
    'pages'    => $pages,
    'email'    => $email,
    'name'     => $name,
    'options'  => isset($input['options']) ? $input['options'] : array(),
    'queuedAt' => time()
);

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

    // Push job onto queue
    $redis->rpush('pdf_jobs', json_encode($job));

    // Store initial status
    $status = array(
        'jobId'     => $jobId,
        'status'    => 'queued',
        'progress'  => 'Waiting in queue...',
        'updatedAt' => time() * 1000
    );
    $redis->setex('pdf_status:' . $jobId, 7200, json_encode($status));

    echo json_encode(array(
        'success' => true,
        'jobId'   => $jobId
    ));

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Queue error: ' . $e->getMessage()));
}