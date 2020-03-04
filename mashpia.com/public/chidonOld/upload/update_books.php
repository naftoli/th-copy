<?php
$admin_auth = ['school'];
require_once __DIR__ . "/../../header.php";
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}

ini_set('display_errors',1);
require __DIR__ . "/../../class.globalSettings.php";
$year = GlobalSettings::getChidonYear();

$qrys = [];
if (($handle = fopen("book_grade_updates.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $qry = "update th_chidon set grade = '" . $data[1] . "', book = '" . $data[2] . "' where user_id = " . $data[0] . " and year = " . $year;
        $qrys[] = $qry;
    }
    fclose($handle);
}
//echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;

foreach ( $qrys as $qry ) {
    if ( !mysql_query( $qry ) ) {
        echo "There was an error - " . $qry . "<br />" . mysql_error();
        $success = false;
        break;
    }
}

if ( $success ) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');
echo "done.";