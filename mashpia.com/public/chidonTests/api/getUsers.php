<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$class_id = $input['class_id'];

// get all platoons for this school
$stmt = $MASHPIA_DB->prepare("
    select * 
    from users 
    where class_id = :class_id 
    and user_registered > 0 
    order by last, first");
$stmt->execute(['class_id' => $class_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users);
?>