<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("
    SELECT school_id, show_report_cards 
    FROM schools 
    WHERE school_id = :id
");

$info = [];
foreach ($schools as $school_id => $school) {
    $stmt->execute(['id' => $school_id]);
    $row = $stmt->fetch();
    $info[$school_id] = $row['show_report_cards'];
}
echo json_encode([
    'success'   => true,
    'info'      => $info
]);