<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// get the school id
$school_id = mysql_real_escape_string($_POST['school_id']);
$checked = mysql_real_escape_string($_POST['checked']);

$sql = "update schools set chidon_5783_updated_shipping = $checked where school_id = $school_id";
if (mysql_query($sql)) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Error updating school.'
    ]);
}