<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300); // update max execution time to 5 min

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$info = file_get_contents("php://input");
$info = json_decode($info, true);
$school_id = $info['school_id'];

$sql = "
    SELECT 
        user_id, first, last, class_grade, class_sub
    FROM
        users u
            JOIN
        classes c USING (class_id)
            JOIN
        user_registration ur USING (user_id)
    WHERE
        u.school_id = :school 
            AND u.user_registered > 0
            AND ur.year = :year
    ORDER BY class_grade , class_sub , last , first";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':school' => $school_id,
    ':year' => $year
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows);