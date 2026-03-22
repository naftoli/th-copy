<?php
//$admin_auth = ['user'];
//require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        first_he, last_he, book
    FROM
        users u
            JOIN
        th_chidon tc USING (user_id)
    WHERE
        user_id = :user AND year = :year");
$res = $stmt->execute([
    'user' => $_POST['user_id'],
    'year' => GlobalSettings::getChidonYear()
]);
if ($res) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $name = $row['first_he'] . ' ' . $row['last_he'];
    if (!empty(trim($name))) {
        echo json_encode([
            'success' => true,
            'heName' => $name,
            'book' => $row['book']
        ]);
        exit;
    }
}
echo json_encode([
    'success'   => false
]);