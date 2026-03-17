<?php
require_once __DIR__ . '/bootstrap.php';

$username = attendance_get_post_string('username');
$password = attendance_get_post_string('password');

if ($username === '' || $password === '') {
    attendance_json_error('Invalid Login');
}

$s = new StaffManager();
if (!$s->checkLogin($username, $password)) {
    attendance_json_error('Invalid Login');
}

attendance_json_ok([
    'login' => encrypt_decrypt('encrypt', $s->getID()),
]);