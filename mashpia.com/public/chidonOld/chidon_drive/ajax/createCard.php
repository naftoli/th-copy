<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../api/header/db.php';

//***************** LOAD CURRENT YEAR **********************/
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$ccInfo = $_POST['ccInfo'];
$admin_id = $_POST['admin_id'];

if (! ($ccInfo && $admin_id)) {
    echo json_encode([
        'success'   => false,
        'error'     => 'You have not provided the info needed to create a new credit card in your profile.'
    ]);
}

require_once __DIR__ . '/../../../api/models/Admin.php';
try {
    $admin = \Admin::find('first', ['admin_id' => $admin_id]);
} catch (Exception $e) {
    echo json_encode([
        'success'   => false,
        'error'     => $e->getMessage()
    ]);
}

if ($admin) {
    $props = [
        'cc-number' => $ccInfo['num'],
        'cc-exp'    => $ccInfo['exp'],
        'x_card_code' => $ccInfo['cvv']
    ];
    $created = $admin->createPaymentProfile( $props );
    if (! is_array($created)) {
        echo json_encode([
            'success'   => false,
            'error'     => $created
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'card_id' => $created['customerPaymentProfileId']
        ]);
    }
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'Error finding admin info in database.'
    ]);
}