<?php
ini_set('display_errors',1);
require_once __DIR__ . '/bootstrap.php';

$sm = attendance_require_staff();

$type = attendance_get_post_string('type');
$timeId = attendance_get_post_string('time_id');
$groups = attendance_get_post_array('groups');

if ($type === '' || $timeId === '' || empty($groups)) {
    attendance_json_error('Invalid Request', $_POST);
}

if (!$sm->assertGroupsAllowed($groups)) {
    attendance_json_error('Not authorized for one or more selected groups');
}

$marks = $sm->getChildren($timeId, $type, $groups);

attendance_json_ok([
    'type' => $type,
    'marks' => $marks,
]);