<?php
include($_SERVER['DOCUMENT_ROOT']."/reports/inc/header.php");
// parse the params...
$th_chidon_chap_id = clean_post_param("chap_id");

if(!$th_chidon_chap_id){
    render_json_error("Error CH-CHAP-002: Invalid Paramaters");
}

$chap_query = mysql_query(
     " SELECT th_chidon_chap_id AS chap_id, first_name, last_name, phone, email, dob, sweater_size, "
    ." acc_name, acc_address, acc_cross_st, acc_phone, vehicle "
    ." FROM th_chidon_chaps WHERE th_chidon_chap_id = '$th_chidon_chap_id';"
);

if(!$chap_query || mysql_num_rows($chap_query) == 0 ) {
    render_json_error("Error CH-CHAP-003: Could not load Chaperone");
}

echo json_encode([
    "success" => true,
    "chaperone" => mysql_fetch_assoc($chap_query) // return the first row to the client...
]);