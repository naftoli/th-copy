<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$i = 1;
$qrys = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        where deleted = 0 
        and (khk = 1 or school_rep = 1 or trophy_contestant = 1 or contestant = 1) 
        and year = " . $year . " 
        order by tc.grade, tc.school_id, last, first";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $identifier = '_';
    $cert_id = $row['grade'] . $identifier . $row['school_id'] . $identifier . $i++;
    $qrys[] = "update th_chidon set cert_number = '" . $cert_id . "' where th_chidon_id = " . $row['th_chidon_id'];
}

foreach ( $qrys as $qry ) mysql_query( $qry );
echo "done.";