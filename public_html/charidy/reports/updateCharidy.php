<?php
ini_set('display_errors',1);
require '../../db.php';

$charidy_id = mysql_real_escape_string( $_POST['charidy_id'] );
$parent_id = mysql_real_escape_string( $_POST['parent_id'] );

$sql = "update charidy set parent_admin_id = " . $parent_id . " where charidy_id = " . $charidy_id;
if (mysql_query( $sql )) echo 1;
else echo 0;
?>