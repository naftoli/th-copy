<?php
require '../../../db.php';

$chap_id = mysql_real_escape_string( $_POST['id'] );
$sql = "delete from th_chidon_chaps where th_chidon_chap_id = " . $chap_id;
if (mysql_query( $sql )) {
    echo 0;
} else {
    echo $sql . "<br />" . mysql_error();
}