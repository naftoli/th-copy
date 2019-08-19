<?php
//header('Content-Type: application/json');
// DBS connection.....
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );
require_once(dirname(__FILE__)."/functions/header.php");

require_once ( $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();
$year = 5778;

// Authentication scheme
require_once( $_SERVER['DOCUMENT_ROOT'].'/mobile/reg/ajax/encrypt.php' );
$login = encrypt_decrypt('decrypt', $_POST['login']);
if(!$login) render_json_error("Invalid Login");
// get the user
$user_query = mysql_query("SELECT walking_zone, door_number, bus_code FROM th_chidon_staff WHERE staff_id = '$login' LIMIT 1;"); // get the users info
if(!$user_query || mysql_num_rows($user_query) == 0)  render_json_error("Invalid Login");
$user = mysql_fetch_assoc($user_query);

// get the TIME ID
$time_id        = isset($_POST['time_id'])      ? clean_post_param('time_id')   : render_json_error("Invalid Request");
$th_chidon_id   = isset($_POST['chidon_id'])    ? clean_post_param('chidon_id') : render_json_error("Invalid Request");
$checked = isset($_POST['checked']) && $_POST['checked'] == "true"; // set it to true or false....

if($checked) {
    $status = mysql_query(
         " INSERT INTO th_chidon_attendance_marks "
        ." (att_time_id, th_chidon_id, marked, marked_by) "
        ." VALUES ('$time_id', '$th_chidon_id', 1, '".$login."') "
    );
} else {
    $status = mysql_query(
         " DELETE FROM th_chidon_attendance_marks "
        ." WHERE att_time_id = '$time_id' AND th_chidon_id = '$th_chidon_id' "
    );
}

echo json_encode([
    "success"   => $status
]);