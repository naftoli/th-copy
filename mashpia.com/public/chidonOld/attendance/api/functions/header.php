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
    if (!isset($_POST[$param_name]) || is_array($_POST[$param_name])) return '';
    return trim((string)$_POST[$param_name]);
}