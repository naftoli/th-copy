<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $_POST['admin']);

$data = $_POST['data'];
$user_id = $data['user_id'];
$yarmulka = $data['yarmulka'];
$sweater_size = $data['size'];
$recruited_by = $data['recruited'];
$track = $data['track'];

$sql = "update th_chidon set size = '" . $sweater_size . "', test_type = '" . $track . "'";
if ($yarmulka > 0) {
    $sql .= ", yarmulka = " . $yarmulka;
}
if ($recruited_by > 0) {
    $sql .= ", recruited_by = " . $recruited_by;
}
$sql .= " where user_id = " . $user_id . " and year = " . $year;
mysql_query($sql);
echo json_encode([
    'success'   =>  true
]);

//mysql_query('set autocommit=0');
//mysql_query('begin');
//$success = true;
//foreach ($qrys as $qry) {
//    if (!mysql_query($qry)) {
//        $success = false;
//        break;
//    }
//}
//if ($success) {
//    mysql_query('commit');
//    mysql_query('set autocommit=1');
//    echo json_encode([
//        'success'   =>  true
//    ]);
//} else {
//    mysql_query('rollback');
//    mysql_query('set autocommit=1');
//    echo json_encode([
//        'success'   =>  false,
//        'info'      =>  mysql_error(),
//        'error'     =>  'There was an error saving your prizes.'
//    ]);
//}