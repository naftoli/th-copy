<?php
require '../db.php';

$info = array();
$user_id = mysql_real_escape_string( $_POST['user'] );
$sql = "SELECT school_id, class_id, user_id FROM users WHERE (user_serial='$user_id' OR user_id = '$user_id');";
$result = mysql_query($sql);
if (mysql_num_rows($result)) {
    $row = mysql_fetch_assoc($result);
    $info['school'] = $row['school_id'];
    $info['grade'] = $row['class_id'];
    $info['user'] = $row['user_id'];
}
echo json_encode($info);