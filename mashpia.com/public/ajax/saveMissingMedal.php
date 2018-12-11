<?php
require '../db.php';
$info = $_POST['info'];
$arrInfo = explode(':', $info);
$checked = $_POST['checked'];

$i = 0;
$school = $arrInfo[$i++];
$user = $arrInfo[$i++];
$subject = $arrInfo[$i++];
$medal = $arrInfo[$i++];

$sql = "select * from missing_medals
        where school = " . $school . " 
        and user = " . $user . " 
        and subject = \"" . $subject . "\" 
        and medal = \"" . $medal . "\"";
$result = mysql_query( $sql );
if (mysql_num_rows( $result ) > 0) {
    if ($checked == 'false') {
        $sql = "delete from missing_medals
                where school = " . $school . " 
                and user = " . $user . " 
                and subject = \"" . $subject . "\" 
                and medal = \"" . $medal . "\"";
        mysql_query( $sql );
    }
} else {
    if ($checked == 'true') {
        $sql = "insert into missing_medals
                set school = " . $school . ", 
                user = " . $user . ",
                subject = \"" . $subject . "\",
                medal = \"" . $medal . "\"";
        mysql_query( $sql );
    }
}
echo $sql;