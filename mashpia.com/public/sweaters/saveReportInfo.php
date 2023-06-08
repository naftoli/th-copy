<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        'success' => false,
        'message' => 'No access'
    ]);
    exit;
}

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

// save info to database
$qrys = [];
foreach ($data as $row) {
    if ($row['cancel']) {
        // delete sweater
        $qry = "DELETE FROM family_sweaters WHERE family_sweater_id = " . $row['sweater_id'];
        $qrys[] = $qry;
    }
    $qry = "UPDATE family_sweaters SET color = '" . $row['color'] . "', size = '" . $row['size'] .
        "', rank = '" . $row['rank'] . "', cap = '" . $row['cap'] . "', notes = '" . $row['notes'] . "', 
        school_id = " . $row['school'] . ", address = '" . $row['address'] . "' 
        WHERE family_sweater_id = " . $row['sweater_id'];
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
        'message'   => 'Sweater(s) saved successfully.'
    ]);
} else {
    mysql_query('rollback');
    echo json_encode([
        'success'   => false,
        'message'   => 'There was an error saving the sweater(s).',
        'error'     => $qry . '\n' . mysql_error()
    ]);
}