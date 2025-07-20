<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../api/header/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required.']);
    exit;
}

if ($admin_user['auth'] != 'super') {
    echo json_encode(['success' => false, 'message' => 'You are not authorized to do this.']);
    exit;
}

try {
    $stmt = $MASHPIA_DB->prepare("SELECT screen_announcement_id, text, image, image_size, text_size, type, from_date, to_date, limit_to_schools, limit_to_classes FROM screen_announcements ORDER BY screen_announcement_id DESC");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $announcements]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 