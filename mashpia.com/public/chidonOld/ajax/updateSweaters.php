<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$ids = mysql_real_escape_string($_POST['ids']);
$checked = mysql_real_escape_string($_POST['checked']);

$qrys = [];
$school_ids = explode(',', $ids);
foreach ($school_ids as $school_id) {
    $sql = "update schools set sweaters_confirmed_5782 = $checked where school_id = " . $school_id;
    $qrys[] = $sql;
}

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');
foreach ($qrys as $qry) {
    if (! mysql_query($qry)) {
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query('commit');
    mysql_query('set autocommit=1');
    echo json_encode([
        'success'   => true
    ]);
} else {
    mysql_query('rollback');
    mysql_query('set autocommit=1');
    echo json_encode([
        'success'   => false,
        'error'     => mysql_error()
    ]);
}