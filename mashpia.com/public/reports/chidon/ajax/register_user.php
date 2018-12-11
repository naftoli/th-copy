<?php
include(dirname(__FILE__)."/../../inc/header.php");

if ($admin_user['auth'] != 'super') {
    render_json_error("CH-USER-ADD-0101: Invalid Permissions");
}

//***************** PARSE PARAMS **********************/
$user_id    = mysql_real_escape_string($_POST['user_id']);
$school_id  = mysql_real_escape_string($_POST['school_id']);
$t_shirt    = mysql_real_escape_string($_POST['t_shirt']);

if(!$user_id || !$school_id || !$t_shirt){
    render_json_error("CH-USER-ADD-0102: Invalid Paramaters");
}
//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** PREVENT DUPLICATES **********************/
$validate_query = mysql_query("SELECT * FROM th_chidon WHERE user_id='$user_id' AND year='$year'");

if(mysql_num_rows($validate_query) != 0) {
    render_json_error("CH-USER-ADD-0104: Chayol is already Registered");
}
//***************** REGISTER CHAYOL **********************/
$register_sql = "INSERT INTO th_chidon (year, school_id, user_id, size) VALUES ('$year', '$school_id', '$user_id', '$t_shirt')";

$register_query = mysql_query($register_sql);

if(!$register_query){
    render_json_error("CH-USER-ADD-0105: Could not register Chayol. Please contact support for more information.");
}

$chidon_id = mysql_insert_id();

echo json_encode([
    "success"   => true,
    "chidon_id" => $chidon_id
]);

