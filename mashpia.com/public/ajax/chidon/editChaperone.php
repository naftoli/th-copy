<?php
include($_SERVER['DOCUMENT_ROOT']."/reports/inc/header.php");
// parse the params...
$th_chidon_chap_id  = clean_post_param("chap_id");
//$action             = clean_post_param("action");
$school_id          = clean_post_param("school_id");

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

$fields = ['school_id', 'first_name', 'last_name', 'phone', 'email', 'dob', 'chidon_type', 'acc_name', 'acc_address', 'acc_phone', 'vehicle', 'sweater_size', 'chap_type'];

$sql_data = [];
// go through each feild and add it to the dataset...
foreach($fields as $key) {
    $value = clean_post_param($key);
    // if we are missing a paramater, return an error....
    if(!isset($_POST[$key]) && !in_array($key, ["sweater_size"])) { // if the key is blank and not excluded from the requirments...
        render_json_error("Error CH-CHP-010: Empty field. All fields are required.");
    } elseif (isset($_POST[$key]) && $value != "") { // do not add feilds that can be blank to the sql data if they are...
        $sql_data[] = "$key = '$value'";
    } elseif (isset($_POST[$key]) && in_array($key, ["sweater_size"]) && $value == "") { // if sweater is blank set sweater to null
        $sql_data[] = "$key = null";
    }
}

$update_query = mysql_query(
    "UPDATE th_chidon_chaps SET ".implode(", ", $sql_data)." WHERE th_chidon_chap_id='$th_chidon_chap_id'"
);

if(!$update_query){
    render_json_error("Error CH-CHP-011: Could not update chaperone.");
}

// if sweater was removed, remove sweater size

// let the user know that editing is done...
echo json_encode(["success" => true, "sql" => "UPDATE th_chidon_chaps SET ".implode(", ", $sql_data)." WHERE th_chidon_chap_id='$th_chidon_chap_id'"]); die();