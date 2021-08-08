<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$school = mysql_real_escape_string($_POST['school']);
$method = mysql_real_escape_string($_POST['method']);
$type = mysql_real_escape_string($_POST['type']);
$amount = floatval(mysql_real_escape_string($_POST['amount']));

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear( $school );

// find id in school_registrations table
$sql = "select school_registration_id from school_registrations where school_id = " . $school . " and year = " . $year;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_assoc($result);
    $id = $row['school_registration_id'];

    // add payment
    $sql = "insert into school_registration_details 
            set school_registration_id = " . $id . ", 
            type = '" . $type . "', 
            amount = " . $amount . ", 
            method_of_payment = '" . $method . "', 
            school_id = " . $school . ", 
            year = " . $year . ", 
            date_paid = now()";
    $result = mysql_query($sql);
    if ($result) {
        echo json_encode([
            'success'   => true
        ]);
        exit;
    } else {
        echo json_encode([
            'success'   => false,
            'error'     => 'There was an error adding the payment.'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false, 
        'error'     => 'This school had not yet registered for this year. Payments can only be added once school is registered.'
    ]);
    exit;
}
