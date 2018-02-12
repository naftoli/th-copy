<?php
require '../db.php';
$val = mysql_real_escape_string($_POST['val']);
$school = (int)mysql_real_escape_string($_POST['school']);

$sql = "update schools set col_show = " . $val . " where school_id = " . $school;
if (mysql_query($sql)) echo 'Updated.';
else echo 'Error updating.';