<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once(dirname(__FILE__).'/../../../header.php');
if ( $admin_user['auth'] !== "super" ){
    echo json_encode([
        "success" => false,
        "error" => "Invalid Account Permissions. HQ account only"
    ]); 
    die();
}
// load the current year
require_once(dirname(__FILE__).'/../../../class.globalSettings.php');
$year = GlobalSettings::getCurrentYear(); 

$caller_id = mysql_real_escape_string( $_POST['caller_id'] );

$donor_ids = [];
foreach ( $_POST['donor_ids'] as $donor_id ) {
    $donor_ids[] = mysql_real_escape_string( $donor_id );
};

foreach( $donor_ids as $donor_id ) {
    mysql_query (
        "INSERT INTO charidy_donors_callers (donor_id, charidy_caller_id, year) "
        ." VALUES('$donor_id', '$caller_id', '$year') ON DUPLICATE KEY UPDATE charidy_caller_id='$caller_id'"
    );
}

echo json_encode( [ "success" => true ] );