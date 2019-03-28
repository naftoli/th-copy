<?php
require 'db.php';
require 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$file = fopen("chidonReg5.csv", "r");
$contents = stream_get_contents($file);
$arrRows = preg_split("/[\n\r]+/", $contents);

foreach ($arrRows as $strLine) {
    $i = 0;
    $data = explode(",", $strLine);
    $serial = $data[$i++];
    $sweater = $data[$i++];
    
    $sql = "select user_id, school_id from users
            where user_serial = " . $serial;
    $result = mysql_query( $sql );
    if (mysql_num_rows( $result )) {
        $row = mysql_fetch_assoc( $result );
    } else {
        echo $sql . "<br />";
        continue;
    }    
    
    $sql = "insert into th_chidon
            set year = " . $year . ",
            school_id = " . $row['school_id'] . ",
            user_id = " . $row['user_id'] . ",
            size = '" . $sweater . "',
            reg_date = now()";
    //echo $sql . "<br />";
    mysql_query( $sql ) or die( $sql . "<br />" . mysql_error() );
}
echo "done.";