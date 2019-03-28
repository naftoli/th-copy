<?php
include(dirname(__FILE__)."/../../inc/header.php");

if ($admin_user['auth'] != 'super') {
    render_json_error("CH-USER-ADD-0101: Invalid Permissions");
}

//***************** PARSE PARAMS **********************/
$user_serial    = mysql_real_escape_string($_POST['user_serial']);
$chidon_id      = mysql_real_escape_string($_POST['chidon_id']);

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

if(!$user_serial && !$chidon_id){
    render_json_error("CH-USER-ADD-0102: Invalid Paramaters");
}

if($user_serial) {
    $filter = " WHERE user_serial = '$user_serial' ";
} else {
    $filter = " WHERE tc.th_chidon_id = '$chidon_id' ";
}

$user_sql = " SELECT users.user_id, first, last, gender, user_registered, th_chidon_id, admin_id, users.school_id, "
    ." tc.size, tc.host, tc.host_address1, tc.host_address2, tc.between_streets, tc.host_number, tc.date_paid "
    ." FROM users LEFT JOIN th_chidon tc ON tc.user_id = users.user_id AND year = '$year' "
    ." LEFT JOIN admin_auths aa ON aa.id = users.user_id AND auth = 'user' "
    .$filter;
    
$user_query = mysql_query($user_sql);

if(mysql_num_rows($user_query) == 0){
    render_json_error("CH-USER-ADD-0103: User Does Not Exist");
}

$user_row = mysql_fetch_assoc($user_query);

echo json_encode([
    "success"   => true,
    "user"      => $user_row
]);