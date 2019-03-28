<?php
require '../../../db.php';

$admin_id = mysql_real_escape_string( $_POST['admin_id'] );
$value = mysql_real_escape_string( $_POST['value'] );
$field = mysql_real_escape_string( $_POST['field'] );

$sql = "select * from th_donor_list where admin_id = " . $admin_id;
$result = mysql_query( $sql );
if (mysql_num_rows( $result )) {
    if ($field == 'notes' || $field == 'assigned') {
        $sql = "update th_donor_list set " . $field . " = \"" . urldecode($value) . "\" where admin_id = " . $admin_id;
    } else {
        $sql = "update th_donor_list set " . $field . " = " . floatval($value) . " where admin_id = " . $admin_id;
    }
} else {
    // only insert if we are assigning first time
    if ($field == 'assigned') {
        $sql = "insert into th_donor_list
                set admin_id = " . $admin_id . ",
                assigned = '" . urldecode($value) . "'";
    }
}
//echo $sql;
if (mysql_query( $sql )) {
    echo 0;
} else {
    echo $sql . "<br />" . mysql_error();
}