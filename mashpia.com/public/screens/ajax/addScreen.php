<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

function generateSlug($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
}

function cleanUrl($url) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $url), '-'));
}

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.adminSchools.php';
header('Content-Type: application/json');

$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();

// Validate required fields
$school_id = $_POST['school_id'] ?? '';
$screen_name = $_POST['screen_name'] ?? '';
$screen_size = $_POST['screen_size'] ?? '';
$url = $_POST['url'] ?? '';
$password = $_POST['password'] ?? '';

// Validate all required fields
if (empty($school_id)) {
    echo json_encode(['success' => false, 'message' => 'School is required']);
    exit;
}

if (empty($screen_name)) {
    echo json_encode(['success' => false, 'message' => 'Screen name is required']);
    exit;
}

if (empty($screen_size)) {
    echo json_encode(['success' => false, 'message' => 'Screen size is required']);
    exit;
}

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password is required']);
    exit;
}

// Store password as plain text (no hashing)
$plain_password = $password;

// If URL is empty, generate from screen name
if (empty($url)) {
    $url = generateSlug($screen_name);
} else {
    // Clean the provided URL
    $url = cleanUrl($url);
}

// check if url is already in use
$existing_url = $MASHPIA_DB->prepare("SELECT url FROM screens WHERE url = ? AND school_id = ?");
$existing_url->execute([$url, $school_id]);
$res = $existing_url->fetch(PDO::FETCH_ASSOC);
if ($res) {
    echo json_encode(['success' => false, 'message' => 'URL already in use']);
    exit;
}

try {
    $sql = "INSERT INTO screens (school_id, screen_name, url, screen_size, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $MASHPIA_DB->prepare($sql);
    $res = $stmt->execute([$school_id, $screen_name, $url, $screen_size, $plain_password]);
    
    if ($res) {
        echo json_encode(['success' => true, 'message' => 'Screen created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create screen']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}