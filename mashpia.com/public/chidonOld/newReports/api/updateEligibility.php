<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$input = json_decode(file_get_contents('php://input'), true);
$serial = $input['serial'];
$checked = $input['checked'];

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$sql = "SELECT * FROM users WHERE user_serial = '" . $serial . "'";
$result = mysql_query($sql);
if (mysql_num_rows($result) == 0) {
    die('User not found');
} else {
    // get user ID
    $user = mysql_fetch_assoc($result);
    $user_id = $user['user_id'];
}

$sql = "UPDATE th_chidon SET confirmed_info = :checked WHERE user_id = :user_id and year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
if ($stmt->execute([
    ':checked'   => $checked,
    ':user_id'   => $user_id,
    ':year'      => $year,
])) {
    echo json_encode([
        'success' => true,
        'error' => null
    ]);
} else {
    $error = $stmt->errorInfo();
    echo json_encode([
        'success' => false,
        'error' => $error[2]
    ]);
}
