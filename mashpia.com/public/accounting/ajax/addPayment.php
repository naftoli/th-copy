<?php
$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$school = $_POST['school'];
$method = $_POST['method'];
$type = $_POST['type'];
$amount = floatval($_POST['amount']);

require_once '../../class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear($school);

// find id in school_registrations table
$sql = "select school_registration_id from school_registrations where school_id = :school and year = :year";
$result = $MASHPIA_DB->prepare($sql);
$result->execute([
    ':school' => $school,
    ':year' => $year
]);
$row = $result->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $id = $row['school_registration_id'];
    // add payment
    $sql = "insert into school_registration_details 
            set school_registration_id = :id, 
            type = :type, 
            amount = :amount, 
            method_of_payment = :method, 
            school_id = :school, 
            year = :year, 
            date_paid = now()";
    $result = $MASHPIA_DB->prepare($sql);
    $res = $result->execute([
        ':id' => $id,
        ':type' => $type,
        ':amount' => $amount,
        ':method' => $method,
        ':school' => $school,
        ':year' => $year
    ]);
    if ($res) {
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