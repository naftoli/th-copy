<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

function generateSlug($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
}

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.adminSchools.php';

$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();

$school_id = $_POST['school_id'];
$screen_name = $_POST['screen_name'];

$url = generateSlug($screen_name);
$url = str_replace(' ', '-', $url);

// check if url is already in use
$existing_url = $MASHPIA_DB->query("SELECT url FROM screens WHERE url = '$url' AND school_id = $school_id");
if ($existing_url->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(['error' => 'URL already in use']);
    exit;
}

$sql = "INSERT INTO screens (school_id, screen_name, url) 
    VALUES ($school_id, '$screen_name', '$url')";
$res = $MASHPIA_DB->query($sql);

echo json_encode(['success' => $res]);