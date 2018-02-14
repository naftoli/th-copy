<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include(dirname(__FILE__)."/../reports/inc/header.php");

if($admin_user['auth'] != 'super') {
	render_json_error("You do not have permission to update this information");
}

// ******************* GET AND CLEAN THE POST PARAMS **************** //
$school_id      = clean_post_param('school_id');
$tanya          = clean_post_param('tanya');
$mishna         = clean_post_param('mishna');
$tanya_campaign = clean_post_param('tanya_campaign');
$mishna_campaign = clean_post_param('mishna_campaign');
$child_count    = clean_post_param('child_count');

// ******************* DETERMINE USERID **************** // 
$user_exists_query = mysql_query(
    " SELECT u.user_id, bpu.campaign_id, bpu.num_lines, bpu.child_count " // select the campaign and the number of lines
    ." FROM users u JOIN bp_user_summary bpu USING (user_id) " // from the bp_user_summary table (joined from users...)
    ." WHERE bpu.campaign_id IN ($tanya_campaign, $mishna_campaign) " // where the campaign is in the current campaigns
    ." AND u.school_id = '$school_id' AND u.class_id IS NULL " // and they are in the same school with NO GRADE!
    ." AND u.username like 'TanyaUser%'" // and the first name is tanya.... (perhaps we should say the child count is greater then 1?)
);
$user_exists_count = mysql_num_rows($user_exists_query); // get the count...

// the user exists if we have a row count...
$user_exists = $user_exists_count > 0;
// create the user if he does not exist...
if(!$user_exists) {
    // ******************* DETERMINE INSERT/UPDATE FOR NEW USER **************** // 
    $count = 0;
    // determine the user_name
    $username = "TanyaUser";
    while (mysql_num_rows(mq('SELECT username FROM users WHERE username = ' . ms($username.$count))))
        $count++;
    $username .= $count;
    // genrate a barcode...
    $count = 0; // reset the count...
    do {
        if ($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
        $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
    // generate a serial number
    $user_serial = mysql_result(mysql_query("(SELECT IFNULL(MAX(user_serial), 0)+1 FROM users users_max)"), 0);
    // echo the sql we will want to run
    $status = mysql_query(
        " INSERT INTO users (user_code, username, email, password, first, last, first_he, last_he, school_id, school_type_id, user_serial, "
        ." user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, kiosk_edit, yan) "
        ." VALUES ('$user_code', '$username', ' ', ' ', 'Tanya', 'Whole School', '', '', '$school_id', '5', '$user_serial', "
        ." '', '', '', '', '', '', '', '', 1)"
    ); // matches line 45
    
    if(!$status) render_json_error("Server Error: Could not create marking user.");
    
    $user_id = mysql_insert_id();
    $insert_campaigns = [$tanya_campaign => $tanya, $mishna_campaign => $mishna];
    $update_campaigns = [];
} else {
    // ******************* DETERMINE INSERT/UPDATE FOR EXISTING USER **************** // 
    $insert_campaigns = [];
    $update_campaigns = [];
    
    if($user_exists_count == 1) {
        $row = mysql_fetch_assoc($user_exists_query);
        $user_id = $row['user_id'];
        // add the campaign we do not have to the $insert_campaigns array and the one we do to the $update_campaigns array
        $row['campaign_id'] == $tanya_campaign ? $insert_campaigns = [$mishna_campaign => $mishna]  : $insert_campaigns = [$tanya_campaign => $tanya]; 
        $row['campaign_id'] == $tanya_campaign ? $update_campaigns = [$tanya_campaign => $tanya]    : $update_campaigns = [$mishna_campaign => $mishna];
    } else {
        $row = mysql_fetch_assoc($user_exists_query);
        $user_id = $row['user_id'];
        $update_campaigns = [$tanya_campaign => $tanya, $mishna_campaign => $mishna];
    };
}

// ******************* INSERT/UPDATE THE DBS **************** //
$success = true;
foreach($insert_campaigns as $campaign => $num_lines) {
    $status = mysql_query(
        "INSERT INTO bp_user_summary VALUES ('$campaign', '$user_id', '$num_lines', '$child_count')"
    );
    if(!$status) $success = false; // make sure that the query worked...
}

if(!$success) render_json_error("Server Error: Could not create campaign marks.");

foreach($update_campaigns as $campaign => $num_lines) {
    $status = mysql_query(
        "UPDATE bp_user_summary SET num_lines='$num_lines', child_count='$child_count' "
        ." WHERE campaign_id='$campaign' AND user_id = '$user_id'"
    );
    if(!$status) $success = false; // make sure that the query worked...
}

if(!$success) render_json_error("Server Error: Could not update campaign marks.");

echo json_encode([
    "success" => $success,
    "insert" => $insert_campaigns,
    "update" => $update_campaigns
]);