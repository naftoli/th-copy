<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . "/header.php";

$qrys = [];
$missing = $_POST['missing'];
foreach ($missing as $user_id => $items) {
    $sql = "insert into chidon_missing_items set user_id = " . mysql_real_escape_string($user_id) . ", items = '" .
        json_encode(mysql_real_escape_string($items)) . "'";
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
if ($success) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');

echo $success ? 1 : 0;