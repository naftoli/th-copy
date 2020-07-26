<?php
require '../../../db.php';
require '../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = $_POST['data'];
$qrys = [];
foreach ($info as $refund) {
    $id = mysql_real_escape_string($refund['id']);
    $amount = floatval(mysql_real_escape_string($refund['amount']));
    $sql = "update th_chidon set shabbaton_refund = " . $amount . " where date_paid > 0 and user_id = " . $id . " and year = " . $year;
    $qrys[] = $sql;
}

$success = true;
mysql_query("set autocommit = 0");
mysql_query("begin");
foreach ($qrys as $qry) {
    if (!mysql_query($qry)) {
        $success = false;
        break;
    }
}

if ($success) {
    mysql_query("commit");
    mysql_query("set autocommit = 1");
    echo json_encode([
        'success'   =>  true
    ]);
} else {
    mysql_query("rollback");
    mysql_query("set autocommit = 1");
    echo json_encode([
        'success'   =>  false,
        'info'      =>  mysql_error() . "\n" . $qry,
        'error'     =>  'There was an error saving to database.'
    ]);
}