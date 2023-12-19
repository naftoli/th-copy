<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("
    SELECT school_id, show_report_cards, show_report_card_2, show_report_card_3  
    FROM schools 
    WHERE school_id = :id
");

$info = [];
foreach ($schools as $school_id => $school) {
    $stmt->execute(['id' => $school_id]);
    $row = $stmt->fetch();
    $info[$school_id] = [
        1 => $row['show_report_cards'],
        2 => $row['show_report_card_2'],
        3 => $row['show_report_card_3']
    ];
}
echo json_encode([
    'success'   => true,
    'info'      => $info
]);