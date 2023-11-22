<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

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
        break;
    case 'users':
        // get all users for this platoon
        $stmt = $MASHPIA_DB->prepare("
            select * 
            from users u 
            join th_chidon tc using (user_id) 
            where class_id = :id 
            and user_registered > 0 
            and tc.year = :year 
            order by last, first");
        break;
}
$stmt->execute([
    'id'    => $id,
    'year'  => $year
]);
$info = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($info);