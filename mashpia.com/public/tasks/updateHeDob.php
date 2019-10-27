<?php
require '../db.php';

$qrys = [];
$sql = "select * from users where user_registered > 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $heDob = $row['dob_he'];
    if ( !empty( $row['dob'] ) && !preg_match('/[א-ת]/', $heDob) ) {
        $arrDOB = explode('-', $row['dob']); 
        $jd = gregoriantojd($arrDOB[1], $arrDOB[2], $arrDOB[0]);
        //echo "JD: " . $jd . "<br />";
        $dobHe = jdtojewish( $jd, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH );
        $dobHe = iconv ('WINDOWS-1255', 'UTF-8', $dobHe);
        //echo "HE dob: " . $dobHe . "<br />";
        $qrys[] = "update users set dob_he = '" . addslashes( $dobHe ) . "' where user_id = " . $row['user_id'];
    }
}

foreach ( $qrys as $sql ) {
    //echo $sql;
    mysql_query( $sql );
}
echo "done.";