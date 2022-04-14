<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
$year = GlobalSettings::getChidonYear();

$qrys = [];
$missing = json_decode($_POST['missing']);
foreach ($missing as $user_id => $items) {
    // make sure to escape single quote (apostrophe)
    $items = str_replace("'", "\\\'", $items);
    if (empty($items)) $sql = "delete from chidon_missing_items where user_id = " . $user_id . " and year = " . $year;
    else $sql = "insert into chidon_missing_items set user_id = " . mysql_real_escape_string($user_id) . ", items = '" .
        json_encode($items) . "', year = " . $year . " on duplicate key update items = '" . json_encode($items) . "'";
    $qrys[] = $sql;
}
echo "<pre>"; print_r($qrys); echo "</pre>"; exit;

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');
foreach ($qrys as $qry) {
    if (! mysql_query($qry)) {
        $error = $qry . "<br />" . mysql_error();
        $success = false;
        break;
    }
}
if ($success) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');

echo $success ? 1 : $error;