<?php
include(dirname(__FILE__)."/../../inc/header.php");

require_once ( $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    render_json_error("CH-ZONE-EDIT-0101: Invalid Permissions");
}

//***************** PARSE PARAMS **********************/
$type           = mysql_real_escape_string( $_POST['type'] );
$id             = mysql_real_escape_string( $_POST['id'] );
$walking_zone   = mysql_real_escape_string( $_POST['walking_group'] );

if(!$walking_zone || !$id){
    render_json_error("CH-ZONE-EDIT-0102: Invalid Paramaters");
}
//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** REGISTER CHAYOL **********************/
if ($type == "user")
    $update_sql = "UPDATE th_chidon SET walking_zone='$walking_zone' WHERE th_chidon_id='$id';";
else if ($type == "bunk")
    $update_sql = "UPDATE th_chidon_bunks SET walking_zone='$walking_zone' WHERE bunk_id='$id';";
else if ($type == "chap")
    $update_sql = "UPDATE th_chidon_chaps SET walking_zone='$walking_zone' WHERE th_chidon_chap_id='$id';";

$update_query = mysql_query($update_sql);

if(!$update_query){
    render_json_error("CH-ZONE-EDIT-0112: Could not update Walking Zone. Please contact support for more information.", $update_sql);
}

// if there is a chap, attempt to update their login info
if( $type === "chap" ) {
    $update_login_query = mysql_query(
         " UPDATE th_chidon_staff tcs "
        ." JOIN th_chidon_chaps tcc ON tcs.name = tcc.name AND tcs.year = tcc.year "
        ." SET tcs.walking_zone=tcc.walking_zone "
        ." WHERE tcc.year='$year' AND tcc.th_chidon_chap_id='$id' "
    );
}

echo json_encode([
    "success"   => true,
]);
