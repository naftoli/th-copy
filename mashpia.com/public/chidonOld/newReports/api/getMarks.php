<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$chidon_id = $_GET['chidon_id'];
$yr = $_GET['yr'];

if (!$chidon_id || !$yr) {
    echo json_encode([
        'success' => false,
        'error' => 'No Chidon ID and Year provided.'
    ]);
    exit;
}

$marks = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        * 
    FROM
        th_chidon_marks 
    WHERE
        th_chidon_id = :chidon_id AND year = :yr
    ORDER BY
        th_chidon_id, test_type, test_number
");
$stmt->execute([
    ':chidon_id' => $chidon_id,
    ':yr' => $yr
]);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $marks[$row['test_type']][] = $row;
}
echo json_encode([
    'success' => true,
    'marks' => $marks
]);
exit;