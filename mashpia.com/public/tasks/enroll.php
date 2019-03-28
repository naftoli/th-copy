<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

echo "<pre>";

$class_id = "5801";
$subject_id = "40";
$level = "8";

$users_sql = "SELECT user_id FROM users WHERE class_id=$class_id;";
$users_query = mysql_query($users_sql);
while($row = mysql_fetch_assoc($users_query)){
    $user_id = $row['user_id'];
    $check_user_sql = "SELECT * FROM user_tracks WHERE user_id=$user_id AND subject_id=$subject_id;";
    if(mysql_num_rows(mysql_query($check_user_sql)) == 0){
        echo "Updating $user_id, Enrolling them in $subject_id at $level - ";
        $update_sql = "INSERT INTO user_tracks VALUES ($user_id, $subject_id, 1, $level, 1);";
        echo (mysql_query($update_sql) ? "✔" : "x")."\n";
    }
}