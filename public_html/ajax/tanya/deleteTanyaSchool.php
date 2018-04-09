<?php
include(dirname(__FILE__)."/../../reports/inc/header.php");

if($admin_user['auth'] != 'super') {
	render_json_error("You do not have permission to delete this information");
}

$school_id      = clean_post_param('school_id');

if( !$school_id ) render_json_error("Inavlid Paramaters");

// only delete the school if they are not chayolei and are tanya
$delete_query = mysql_query(
    "UPDATE schools SET tanya = 0 WHERE school_id='$school_id' AND chayolei = 0 AND tanya = 1;"
);

if ( !$delete_query ){
    render_json_error("Error: Could not delete the school");
} else {
    echo json_encode([
        "success" => true
    ]);
};