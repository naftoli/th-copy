<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

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

$admin = $data['admin'];
$sweaters = $data['sweaters'];
$assignedChild = mysql_real_escape_string($data['assignedChild']);
$choice = $data['choice'];
if ($choice == 'address') $address = mysql_real_escape_string(implode(', ', $data['address']));
else if ($choice == 'pickup') $address = 'pickup';

if (count($sweaters) == 0) {
    echo json_encode([
        'success'   => false,
        'message'   => 'You must choose at least one sweater.'
    ]);
    exit;
} else if (!$choice && !$assignedChild) {
    echo json_encode([
        'success'   => false,
        'error'     => 'You must choose a child to assign the sweater(s) to.'
    ]);
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin);

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$qrys = [];

// first remove existing sweaters for this admin
$sql = "delete from family_sweaters where year = $year and admin_id = $admin_id";
$qrys[] = $sql;

// now insert the new sweaters
foreach ($sweaters as $child) {
    $size = mysql_real_escape_string($child['size']);
    $color = mysql_real_escape_string($child['color']);
    $rank = mysql_real_escape_string($child['rank']);
    $cap = mysql_real_escape_string($child['cap']);
    $sql = "insert into family_sweaters (year, admin_id, user_id, size, color, rank, cap, address) 
        values ($year, $admin_id, $assignedChild, '$size', '$color', '$rank', '$cap', '$address')";
    $qrys[] = $sql;
}

$success = true;
mysql_query('set autocommit = 0');
mysql_query('start transaction');
foreach ($qrys as $sql) {
    if (!mysql_query($sql)) {
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
        'error'     => $sql . '\n' . mysql_error()
    ]);
}
mysql_query('set autocommit = 1');
