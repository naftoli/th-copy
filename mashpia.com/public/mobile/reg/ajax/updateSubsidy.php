<?php
if (! isset($_COOKIE['admin'])) {
    exit;
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = $_POST['info'];
$success = true;
foreach ($info as $child) {
    if (is_numeric($child['payment'])) {
        $sql = "update th_chidon set payment_request = " . $child['payment'] . " where user_id = " . $child['user_id'] . " 
        and year = " . $year;
        if (! mysql_query($sql)) {
            $success = false;
            break;
        }
    }
}

if ($success) {
    echo json_encode([
        'success'   => true,
        'msg'       => 'Your subsidy request has been received.'
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'There was an error processing your request.'
    ]);
}

