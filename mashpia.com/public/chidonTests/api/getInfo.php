<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'];
$table = $input['table'];

$info = [];
switch ($table) {
    case 'classes':
        // get all platoons for this school
        $stmt = $MASHPIA_DB->prepare("
            select * 
            from classes 
            where school_id = :id
            and class_era = 0 
            and class_grade >= '4' 
            and class_grade <= '8' 
            order by class_grade, class_sub");
        $stmt->execute(['school_id' => $id]);
        break;
    case 'users':
        // get all platoons for this school
        $stmt = $MASHPIA_DB->prepare("
            select * 
            from users 
            where class_id = :class_id 
            and user_registered > 0 
            order by last, first");
        $stmt->execute(['class_id' => $id]);
        break;
}
$info = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($info);