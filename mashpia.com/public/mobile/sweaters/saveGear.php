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
    $user_id = $child['user_id'];
    $size = $child['size'];
    $color = $child['color'];
    $rank = $child['rank'];
    $sql = "insert into family_sweaters (year, admin_id, user_id, size, color, rank) values ($year, $admin_id, $user_id, '$size', '$color', '$rank')";
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
