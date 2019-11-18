<?php
ini_set('display_errors',1);
require '../../../db.php';
require_once '../../../class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$users = [];
$sql = "select * from th_chidon where parent_id = 0 and year = $year";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[] = $row['user_id'];
}

foreach ( $users as $id ) {
    $sql = "select admin_id from admin_auths where id = " . $id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $sql2 = "update th_chidon set parent_id = " . $row['admin_id'] . " where user_id = " . $id . " and year = $year";
    mysql_query( $sql2 );
}
echo "done.";