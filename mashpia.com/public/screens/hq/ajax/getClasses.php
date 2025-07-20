<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../api/header/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([]);
    exit;
}

if ($admin_user['auth'] != 'super') {
    echo json_encode([]);
    exit;
}

$school_id = $_POST['school_id'] ?? null;
if (!$school_id) {
    echo json_encode([]);
    exit;
}

try {
    $info = [];
    $stmt = $MASHPIA_DB->prepare("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = ? ORDER BY class_grade, class_sub");
    $stmt->execute([$school_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($classes as $class) {
        $info[] = [
            'id' => $class['class_id'],
            'name' => $class['class_grade'] . (empty($class['class_sub']) ? '' : '-' . $class['class_sub'])
        ];
    }
    echo json_encode($info);
} catch (Exception $e) {
    echo json_encode([]);
}