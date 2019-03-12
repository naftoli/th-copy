<?php
//header('Content-Type: application/json');
// DBS connection.....
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );
require_once(dirname(__FILE__)."/functions/header.php");

require_once ( $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();
$year = 5778;

function get_times($type, $gender) {
    $times = [];
    $times_query = mysql_query(
        "SELECT * FROM th_chidon_attendance_times WHERE att_type = '$type' AND archived = 0 AND gender = '$gender'"
    );
    while( $time_entry = mysql_fetch_assoc($times_query) ) {
        $times[] = [
            "key" => $time_entry['att_time_id'],
            "type" => $time_entry['att_type'],
            "description" => $time_entry['description']
        ];
    }
    
    return $times;
}

// Authentication scheme
require_once( $_SERVER['DOCUMENT_ROOT'].'/mobile/reg/ajax/encrypt.php' );
$login = encrypt_decrypt('decrypt', $_POST['login']);
if(!$login) render_json_error("Invalid Login", $_POST);

$user_query = mysql_query("SELECT walking_zone, chidon_type FROM th_chidon_staff WHERE staff_id = '$login' LIMIT 1;"); // get the users info

if(!$user_query || mysql_num_rows($user_query) == 0)  render_json_error("Invalid Login");

$user = mysql_fetch_assoc($user_query);
$user_gender = $user['chidon_type'] == "boys" ? "M" : "F";

$times = [];    $marks = [];

if( $user['walking_zone'] ) {
    $walking_times = get_times('walk', $user_gender);
    
    $times = array_merge($times, $walking_times);
}

if( $user['chidon_type'] ) {
    $chap_times = get_times('chap', $user_gender);
    $times = array_merge($times, $chap_times);
}

echo json_encode([
    "success"   => true,
    "times"     => $times,
]);