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

$id = $_POST['screen_announcement_id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing announcement ID.']);
    exit;
}

try {
    $stmt = $MASHPIA_DB->prepare('DELETE FROM screen_announcements WHERE screen_announcement_id = ?');
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 