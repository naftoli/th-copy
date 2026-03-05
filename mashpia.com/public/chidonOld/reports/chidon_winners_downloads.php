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

$teams = isset($_POST['team']) ? (array) $_POST['team'] : [];
$grades = isset($_POST['grade']) ? (array) $_POST['grade'] : [];
$gender = isset($_POST['gender']) ? strtoupper(trim($_POST['gender'])) : '';

$grade_placeholders = [];
foreach (array_map('intval', $grades) as $i => $g) {
    $grade_placeholders[] = ':grade' . $i;
}
$grade_list = implode(',', $grade_placeholders ?: ['0']);

$sql = "SELECT * FROM th_chidon_winners tcw 
        JOIN users u ON u.user_serial = tcw.serial 
        JOIN th_chidon tc ON tc.user_id = u.user_id 
        WHERE tcw.year = " . intval($year) . " 
        AND tcw.grade IN (" . $grade_list . ") ";
if (in_array($gender, ['F', 'M'])) {
    $sql .= "AND tcw.gender = :gender ";
}

// figure out the team sql
$blue_trophy = 0;
$teamSql = [];
$trophies = [];
$khk_trophies = [];

foreach ($teams as $team) {
    switch ($team) {
        case 'Mishne Torah':
        case 'Sefer Hamitzvos':
            $teamSql[] = $team;
            break;
        case 'Blue Trophy':
            $blue_trophy = 1;
            break;
        case 'Gold Trophy':
        case 'Silver Trophy':
        case 'Bronze Trophy':
            $trophy = explode(' ', $team)[0];
            $trophies[] = $trophy;
            break;
        case 'KHK Gold Trophy':
        case 'KHK Silver Trophy':
        case 'KHK Bronze Trophy':
            $trophy = explode(' ', $team)[1];
            $khk_trophies[] = $trophy;
            break;
    }
}

if ($blue_trophy) 
    $sql .= " AND blue_trophy = 1";
if (!empty($teamSql)) {
    $sql .= " AND tcw.team IN (" . implode(',', array_map(function ($t) use ($MASHPIA_DB) {
        return $MASHPIA_DB->quote($t);
    }, $teamSql)) . ")";
}
if (!empty($trophies)) {
    $sql .= " AND tcw.trophy IN (" . implode(',', array_map([$MASHPIA_DB, 'quote'], $trophies)) . ")";
}
if (!empty($khk_trophies)) {
    $sql .= " AND tcw.khk_trophy IN (" . implode(',', array_map([$MASHPIA_DB, 'quote'], $khk_trophies)) . ")";
}
$sql .= " GROUP BY tcw.serial";

$stmt = $MASHPIA_DB->prepare($sql);
$params = [];
foreach (array_map('intval', $grades) as $i => $g) {
    $params[':grade' . $i] = $g;
}
if (in_array($gender, ['F', 'M'])) {
    $params[':gender'] = $gender;
}
$stmt->execute($params);
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
$filename = 'chidon_pics_' . $gender . ".zip";
createZip($imgs, $filename);