<?php
include(dirname(__FILE__)."/../../inc/header.php");

if ($admin_user['auth'] != 'super') {
    render_json_error("CH-ZONE-EDIT-0101: Invalid Permissions");
}

//***************** PARSE PARAMS **********************/
$th_chidon_id       = clean_post_param('th_chidon_id');
$host               = clean_post_param('host');
$host_address1      = clean_post_param('host_address1');
$host_address2      = clean_post_param('host_address2');
$between_streets    = clean_post_param('between_streets');
$host_number        = clean_post_param('host_number');

if(!$th_chidon_id || !$host || !$host_address1 || !$host_address2 || !$between_streets || !$host_number){
    render_json_error("CH-ZONE-EDIT-0102: Invalid Paramaters");
}
//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** REGISTER CHAYOL **********************/
$update_sql = "UPDATE th_chidon SET "
    ." host='$host', host_address1='$host_address1', host_address2='$host_address2', "
    ." between_streets='$between_streets', host_number='$host_number' "
    ." WHERE th_chidon_id='$th_chidon_id';";

$update_query = mysql_query($update_sql);

if(!$update_query){
    render_json_error("CH-ZONE-EDIT-0112: Could not update Host Info. Please contact support for more information.", $update_sql);
}

echo json_encode([
    "success"   => true,
]);
