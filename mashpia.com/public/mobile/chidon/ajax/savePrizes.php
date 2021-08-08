<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $_POST['admin']);

//echo "<pre>"; print_r($_POST['cart']); echo "</pre>";
$cart = $_POST['cart'];

$qrys = [];
foreach ($cart as $user_id => $items) {
    $qrys[] = "delete from chidon_user_prizes where user_id = " . $user_id . " and year = " . $year;
    foreach ($items as $item) {
        $qrys[] = "insert into chidon_user_prizes set 
                    user_id = " . $user_id . ", 
                    prize_id = " . $item['prize_id'] . ", 
                    he_name = '" . $item['he_name'] . "', 
                    year = " . $year;
    }
}

mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;
foreach ($qrys as $qry) {
    if (!mysql_query($qry)) {
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query('commit');
    mysql_query('set autocommit=1');
    echo json_encode([
        'success'   =>  true
    ]);
} else {
    mysql_query('rollback');
    mysql_query('set autocommit=1');
    echo json_encode([
        'success'   =>  false,
        'info'      =>  mysql_error(),
        'error'     =>  'There was an error saving your prizes.'
    ]);
}