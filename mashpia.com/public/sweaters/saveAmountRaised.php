<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        'success' => false,
        'message' => 'No access'
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = file_get_contents('php://input');
$data = json_decode($info, true);

// Check if the JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    // Handle JSON decoding error
    echo json_encode([
        'success' => false,
        'message' => 'Error decoding JSON data'
    ]);
    exit;
}

$qrys = [];
foreach ($data as $row) {
    $qry = "INSERT IGNORE INTO family_raised  
            SET amount = " . $row['amount'] . ", 
            admin_id = " . $row['admin_id'] . ", 
            year = $year 
            ON DUPLICATE KEY UPDATE amount = " . $row['amount'];
    $qrys[] = $qry;
}

$success = true;
mysql_query('set autocommit = 0');
mysql_query('start transaction');
foreach ($qrys as $qry) {
    if (!mysql_query($qry)) {
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query('commit');
    echo json_encode([
        'success'   => true,
        'message'   => 'Amount(s) saved successfully.'
    ]);
} else {
    mysql_query('rollback');
    echo json_encode([
        'success'   => false,
        'message'   => 'Error saving amount(s).',
        'error'     => mysql_error() . "\n" . $qry
    ]);
}
mysql_query('set autocommit = 1');
