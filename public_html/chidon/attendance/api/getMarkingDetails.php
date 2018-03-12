<?php
//header('Content-Type: application/json');
// DBS connection.....
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );
require_once(dirname(__FILE__)."/functions/header.php");

require_once ( $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();

// Authentication scheme
require_once( $_SERVER['DOCUMENT_ROOT'].'/mobile/reg/ajax/encrypt.php' );
$login = encrypt_decrypt('decrypt', $_POST['login']);
if(!$login) render_json_error("Invalid Login");
// get the user
$user_query = mysql_query("SELECT walking_zone, door_number, chap_chidon_type FROM th_chidon_staff WHERE staff_id = '$login' LIMIT 1;"); // get the users info
if(!$user_query || mysql_num_rows($user_query) == 0)  render_json_error("Invalid Login");
$user = mysql_fetch_assoc($user_query);

// get the TIME ID
$time_id = isset($_POST['time_id']) ? clean_post_param('time_id') : render_json_error("Invalid Request");
// get the type of info to pull
$type_query = mysql_query("SELECT att_type FROM th_chidon_attendance_times WHERE att_time_id = '$time_id' LIMIT 1;"); // get the users info
if(!$type_query || mysql_num_rows($type_query) == 0)  render_json_error("Invalid Request", "SELECT type FROM th_chidon_attendance_times WHERE att_time_id = '$time_id' LIMIT 1;");
$type = mysql_fetch_assoc($type_query)['att_type'];
// array to store the marks in
$marks = [];

if( $type == 'walk' ) {
    $child_list = [];
    $grade_limit = $time_id == "31" ? " AND tc.grade <= '5' " : "";
    $child_list_query = mysql_query(
         " SELECT school_name, first, last, tc.th_chidon_id, user_serial, user_id, walking_zone, "
        ." host, host_address1, host_address2, between_streets, host_number, tcam.marked "
        ." FROM th_chidon tc "
        ." JOIN schools s USING (school_id) "
        ." JOIN users u USING (user_id) "
        ." LEFT JOIN th_chidon_attendance_marks tcam ON tcam.th_chidon_id = tc.th_chidon_id AND tcam.att_time_id = '$time_id' "
        ." WHERE year = '$year' AND walking_zone = '". $user['walking_zone'] ."' "
        .$grade_limit
        ." ORDER BY between_streets, host_address1, host_address2, first, last; "
    );
    while( $child = mysql_fetch_assoc($child_list_query) ) {
        $child_list[] = $child;
    }
    
    $marks = $child_list;
}

//if( $type == 'door' ) {
//    $child_list = [];
//    // TODO limit the grades, 4-5 are door number 1, 2, and 3. 6-8 are door numbers 4, 5 and 6.
//    $min_grade = $user['door_number'] <= 3 ? 4 : 6;
//    $max_grade = $user['door_number'] <= 3 ? 5 : 8;
//    $child_list_query = mysql_query(
//         " SELECT school_name, first, last, tc.th_chidon_id, user_serial, user_id, tc.walking_zone, "
//        ." host, host_number, tcc.name as chap_name, tcc.phone as chap_phone, tcam.marked "
//        ." FROM th_chidon tc "
//        ." JOIN schools s USING (school_id) "
//        ." JOIN users u USING (user_id) "
//        ." LEFT JOIN th_chidon_chaps tcc ON tcc.walking_zone = tc.walking_zone COLLATE utf8_general_ci "
//        ." LEFT JOIN th_chidon_attendance_marks tcam ON tcam.th_chidon_id = tc.th_chidon_id "
//        ." WHERE tc.year = '$year' AND tc.school_id IN ( "
//            ." SELECT tcadsd.school_id FROM th_chidon_attendance_school_doors tcadsd "
//            ." WHERE door_number = '" . $user['door_number'] . "') "
//        ." AND tc.grade >= $min_grade AND tc.grade <= $max_grade "
//        ." GROUP BY u.user_id "
//        ." ORDER BY school_name, last, first; "
//    );
//    while( $child = mysql_fetch_assoc($child_list_query) ) {
//        $child_list[] = $child;
//    }
//    
//    $marks = $child_list;
//}

if( $type == 'chap' ) {
    
    $chap_list = [];
    $chap_list_query = mysql_query(
          " SELECT th_chidon_chap_id, name, phone, email, chidon_type, school_name, walking_zone, tcam.marked "
         ." FROM th_chidon_chaps tcc"
         ." JOIN schools s USING (school_id) "
         ." LEFT JOIN th_chidon_attendance_marks tcam ON tcam.th_chidon_id = tcc.th_chidon_chap_id AND tcam.att_time_id = '$time_id' "
         ." WHERE year='$year' AND chidon_type='" . $user['chap_chidon_type'] . "'"
         ." ORDER BY last_name, first_name "
    );
    
    while( $child = mysql_fetch_assoc($chap_list_query) ) {
        $chap_list[] = $child;
    }
    
    $marks = $chap_list;
}

echo json_encode([
    "success"   => true,
    "type"      => $type,
    "marks"     => $marks,
]);