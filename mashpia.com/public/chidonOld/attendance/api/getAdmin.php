<?php
require_once __DIR__ . '/bootstrap.php';

$sm = attendance_require_staff();
attendance_json_ok([
    'user' => $sm->getPersonalInfo(),
    'info' => $sm->getInfo(),
]);