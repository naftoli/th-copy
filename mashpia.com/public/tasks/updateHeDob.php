<?php
require '../db.php';

$qrys = [];
$users = [];
$sql = "select * from users where user_registered > 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $heDob = $row['dob_he'];
    if ( !preg_match('/[א-ת]/', $heDob) ) {
        $users[] = $row['user_id'];
        $arrDOB = explode('-', $row['dob']);
        $jd = gregoriantojd($arrDOB[1], $arrDOB[2], $arrDOB[0]);
        $dobHe = jdtojewish( $jd, true, CAL_JEWISH_ADD_GERESHAYIM );
        $dobHe = iconv ('WINDOWS-1255', 'UTF-8', $dobHe);
        $qrys[] = "update users set dob_he = '" . $dobHe . "' where user_id = " . $row['user_id'];
    }
}

foreach ( $qrys as $sql ) {
    mysql_query( $sql );
}
echo "done.";