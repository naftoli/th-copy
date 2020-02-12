<?php
// ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . "/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
$year = GlobalSettings::getChidonYear();

foreach ( $_POST as $k => $v ) {
    $_POST[$k] = mysql_real_escape_string( $v );
}

$sql = "INSERT INTO th_chidon_staff 
        SET first_name = '" . $_POST['first_name'] . "', 
        last_name = '" . $_POST['last_name'] . "', 
        cell = '" . $_POST['cell'] . "', 
        email = '" . $_POST['email'] . "', 
        dob = '" . $_POST['dob'] . "', 
        sweater_size = '" . $_POST['s_size'] . "', 
        gender = '" . $_POST['chidon_type'] . "', 
        position = '" . $_POST['position'] . "', 
        acc_name = '" . $_POST['accName'] . "', 
        acc_address = '" . $_POST['accAddress'] . "', 
        acc_phone = '" . $_POST['accPhone'] . "', 
        vehicle = " . $_POST['vehicle'] . ", 
        username = '" . $_POST['email'] . "', 
        password = '5780', 
        year = " . $year;

if ( mysql_query( $sql ) ) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'error'   => mysql_error()
    ]);
}