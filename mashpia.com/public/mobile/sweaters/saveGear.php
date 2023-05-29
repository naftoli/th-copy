<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$info = $_POST['children'];
$admin_id = $_POST['admin'];

require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$qrys = [];
foreach ($info as $child) {
    $user_id = mysql_real_escape_string($child['user_id']);
    $size = mysql_real_escape_string($child['size']);
    $color = mysql_real_escape_string($child['color']);
    $rank = mysql_real_escape_string($child['rank']);
    $school_id = mysql_real_escape_string($_POST['school']);
    $address = !empty($_POST['address']) && is_array($_POST['address']) ? mysql_real_escape_string(implode(' ', $_POST['address'])) :
        mysql_real_escape_string($_POST['address']);
    $sql = "insert into family_sweaters (year, admin_id, user_id, size, color, rank, school_id, address) 
        values ($year, $admin_id, $user_id, '$size', '$color', '$rank', '$school_id', '$address')";
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
