<?php
chdir('../../../');
require 'db.php';

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

$sql = "UPDATE users SET chidon_pic_5782 = '" . mysql_real_escape_string($_POST['chidonPhoto']) . "' WHERE user_id = " . $user_id;
$success = mysql_query( $sql );
echo $success;