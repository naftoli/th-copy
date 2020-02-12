<?php
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
$year = GlobalSettings::getChidonYear();

echo "<pre>"; print_r( $_POST ); echo "</pre>";

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO th_chidon_staff 
    SET first_name = :first, 
    last_name = :last, 
    cell = :number, 
    email = :email, 
    dob = :dob, 
    sweater_size = :sweater, 
    gender = :chidon_type, 
    position = :position, 
    acc_name = :accName, 
    acc_address = :accAddress, 
    add_phone = :accPhone, 
    vehicle = :vehicle, 
    year = :year
");

$res = $stmt->execute([
    ':first'    => $_POST['first_name'], 
    ':last'     => $_POST['last_name'], 
    ':number'   => $_POST['number'], 
    ':email'    => $_POST['email'], 
    ':dob'      => $_POST['dob'], 
    ':sweater'  => $_POST['sweater'], 
    ':chidon_type' => $_POST['chidon_type'], 
    ':position' => $_POST['position'], 
    ':accName'  => $_POST['accName'], 
    ':accAddress'   => $_POST['accAddress'], 
    ':accPhone' => $_POST['accPhone'], 
    ':vehicle'  => $_POST['vehicle'], 
    ':year'     => $year
]);

if ( $res ) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'error'   => $MASHPIA_DB->errorInfo[2]
    ]);
}