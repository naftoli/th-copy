<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

require_once './class.schoolExceptions.php';
$result = SchoolExceptions::updateSchoolExceptions($_REQUEST['prize_id'], $_REQUEST['exceptions']);
$success = $result[0];
$error = $result[1];

if ($success) echo json_encode(['success' => true]);
else echo json_encode(['success' => false, 'error' => $error]);