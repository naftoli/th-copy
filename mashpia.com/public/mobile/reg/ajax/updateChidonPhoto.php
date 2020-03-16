<?php
chdir('../../../');
require 'db.php';

$sql = "UPDATE users SET chidon_pic = '" . mysql_real_escape_string($_POST['chidonPhoto']) . "' WHERE user_id = " . mysql_real_escape_string($_POST['user_id']);
$success = mysql_query( $sql );
echo $success;