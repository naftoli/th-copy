<?php
include(dirname(__FILE__)."/../../inc/header.php");

if ($admin_user['auth'] != 'super') {
    render_json_error("CH-USER-ADD-0101: Invalid Permissions");
}

//***************** PARSE PARAMS **********************/
$th_chidon_id  = mysql_real_escape_string($_POST['th_chidon_id']);
$size    = mysql_real_escape_string($_POST['size']);

if(!$th_chidon_id || !$size){
    render_json_error("CH-USER-ADD-0102: Invalid Paramaters");
}
//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** PREVENT DUPLICATES **********************/
$validate_query = mysql_query("SELECT * FROM th_chidon WHERE th_chidon_id='$th_chidon_id' AND year='$year'");

if(mysql_num_rows($validate_query) == 0) {
    render_json_error("CH-USER-ADD-0111: Chayol is not Registered");
}
//***************** REGISTER CHAYOL **********************/
$size_update_sql = "UPDATE th_chidon SET size='$size' WHERE th_chidon_id='$th_chidon_id' AND year='$year'";

$size_update_query = mysql_query($size_update_sql);

if(!$size_update_query){
    render_json_error("CH-USER-ADD-0112: Could not update Chayol. Please contact support for more information.");
}

echo json_encode([
    "success"   => true,
]);

