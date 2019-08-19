<?php
function render_json_error($error_message, $details = false){
    echo json_encode([
        "success"   => false,
        "error"     => $error_message,
        "details"   => $details
    ]);
    die();
}

function clean_post_param($param_name){
    return mysql_real_escape_string($_POST[$param_name]);
}