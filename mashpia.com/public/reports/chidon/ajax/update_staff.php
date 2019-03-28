<?php
include(dirname(__FILE__)."/../../inc/header.php");

if ($admin_user['auth'] != 'super') {
    render_json_error("CH-STAFF-EDIT-0101: Invalid Permissions");
}

//***************** PARSE PARAMS **********************/
$staff_id   = mysql_real_escape_string( $_POST['staff_id']  );
$column     = mysql_real_escape_string( $_POST['column']    );
$value      = mysql_real_escape_string( $_POST['value']     );

if( in_array($column, ['door_number', 'bus_code', 'chap_chidon_type']) && !$value ){ // allow for blank values to be set to null...
    $value = "NULL";
} else {
    $value = "'" . $value . "'";
}

if(!$staff_id || !$column){
    render_json_error("CH-STAFF-EDIT-0102: Invalid Paramaters");
}
//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** REGISTER CHAYOL **********************/
$update_sql = "UPDATE th_chidon_staff SET $column=$value WHERE staff_id='$staff_id';";

$update_query = mysql_query($update_sql);

if(!$update_query){
    render_json_error("CH-STAFF-EDIT-0112: Could not update Staff. Please contact support for more information.", $update_sql);
}

echo json_encode([
    "success"   => true,
]);

