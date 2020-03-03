<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "select th_chidon_id, book from th_chidon where year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}

$qrys = [];
foreach ( $info as $row ) {
    if ( intval($row['book']) ) $qrys[] = "update th_chidon set grade = " . (intval( $row['book'] ) + 3) . " where th_chidon_id = " . $row['th_chidon_id'];
}

$updated = 0;
foreach ( $qrys as $qry ) {
    mysql_query( $qry ) or die( mysql_error() . "<br />" . $qry );
    $updated++;
}
echo "Updated: " . $updated;