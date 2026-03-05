<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

require_once 'chidon_zip_function.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$team = $_POST['team'] ?? '';
$grade = intval($_POST['grade']) ?? '';
$gender = strtoupper($_POST['gender']) ?? '';

$sql = "SELECT * FROM th_chidon_winners tcw 
        JOIN users u ON u.user_serial = tcw.serial 
        JOIN th_chidon tc ON tc.user_id = u.user_id 
        WHERE tcw.year = " . $year . " 
        AND tcw.grade = :grade 
        AND tcw.gender = :gender";

switch ($team) {
    case 'Mishne Torah':
    case 'Sefer Hamitzvos':
        $sql .= " AND tcw.team = '" . $team . "'";
        break;
    case 'Blue Trophy':
        $sql .= " AND tcw.blue_trophy = 1";
        break;
    case 'Gold Trophy':
    case 'Silver Trophy':
    case 'Bronze Trophy':
        $trophy = explode(' ', $team)[0];
        $sql .= " AND tcw.trophy = '" . $trophy . "'";
        break;
    case 'KHK Gold Trophy':
    case 'KHK Silver Trophy':
    case 'KHK Bronze Trophy':
        $trophy = explode(' ', $team)[1];
        $sql .= " AND tcw.khk_trophy = '" . $trophy . "'";
        break;
}
$sql .= "GROUP BY tcw.serial";

$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    ':grade' => $grade,
    ':gender' => $gender
]);
$info = $stmt->fetchAll(PDO::FETCH_ASSOC);

$imgs = []; 
foreach ( $info as $child) {
    $img_fallbacks = [
        ['from_db' => false, 'val' => $child['khk_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['khk_photo'])],
        ['from_db' => false, 'val' => $child['chidon_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_photo'])],
        ['from_db' => false, 'val' => $child['mobile_pic'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['mobile_pic'])],
        ['from_db' => false, 'val' => $child['chidon_pic_5782'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5782'])],
        ['from_db' => false, 'val' => $child['chidon_pic_5781'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5781'])],
        ['from_db' => true,  'val' => $child['user_photo_id']]
    ];
    $imgs[] = ['filename' => $child['serial'], 'fallbacks' => $img_fallbacks];
}
// echo "<pre>"; print_r($imgs); echo "</pre>"; 
$filename = 'chidonPics.zip';
createZip($imgs, $filename);