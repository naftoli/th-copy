<?php
include(dirname(__FILE__)."/../../inc/header.php");

if ($admin_user['auth'] != 'super') {
    render_json_error("CH-STAFF-EDIT-0101: Invalid Permissions");
}

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** PARSE PARAMS **********************/
$name           = clean_post_param('name');
$cell           = clean_post_param('cell');
$username       = clean_post_param('username');
$password       = clean_post_param('password');
// allow for blank and set it to null...
$walking_zone   = isset($_POST['walking_zone']) ? "'" . clean_post_param('walking_zone') . "'" : "NULL";
//$door_number    = clean_post_param('door_number');
//$bus_code       = clean_post_param('bus_code');
$chidon_type   = isset($_POST['chidon_type']) ? "'" .  clean_post_param('chidon_type') . "'" : "NULL";

if(!$username || !$password){
    render_json_error("CH-STAFF-EDIT-0102: Invalid Paramaters");
}
//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** REGISTER CHAYOL **********************/
$update_sql = " INSERT INTO th_chidon_staff "
    ." (name, cell, username, password, walking_zone, chidon_type, year) VALUES "
    ." ('$name', '$cell', '$username', '$password', $walking_zone, $chidon_type, '$year') ";

$update_query = mysql_query($update_sql);

if(!$update_query){
    render_json_error("CH-STAFF-EDIT-0112: Could not create Staff. Please make sure that your username is unique.", $update_sql);
}

echo json_encode([
    "success"   => true,
]);

