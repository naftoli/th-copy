<?php
chdir('../../../');
require 'db.php';
require 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ( isset( $_POST['serial'] ) ) {
    $sql = "select user_id from users where user_serial = " . mysql_real_escape_string(trim($_POST['serial']));
    $result = mysql_query( $sql );
    if ( mysql_num_rows( $result ) > 0 ) {
        $row = mysql_fetch_assoc( $result );
        $user_id = $row['user_id'];
    } else {
        echo false;
        exit;
    }
} else {
    $user_id = mysql_real_escape_string($_POST['user_id']);
}

$photo_type = $_POST['photo_type'];
$field = $photo_type . '_photo';
$sql = "UPDATE th_chidon SET " . $field . " = '" . mysql_real_escape_string($_POST['chidonPhoto']) . "' WHERE user_id = " . $user_id . " and year = " . $year;
$success = mysql_query( $sql );
echo $success;