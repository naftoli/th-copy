<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$school_id = $_POST['school_id'] ?? '';
$class_id = $_POST['class_id'] ?? '';

if (empty($school_id) || empty($class_id)) {
    echo json_encode(['success' => false, 'message' => 'School and class are required']);
    exit;
}

if (!is_numeric($school_id) || !is_numeric($class_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid school or class ID']);
    exit;
}

try {
    // Get students for the specified school and grade
    $stmt = $MASHPIA_DB->prepare("
        SELECT u.user_id, u.first, u.last 
        FROM users u 
        WHERE u.school_id = :school_id 
        AND u.class_id = :grade_id 
        AND u.user_registered > 0 
        ORDER BY u.last, u.first
    ");
    
    $stmt->execute([
        ':school_id' => $school_id,
        ':grade_id' => $class_id
    ]);
    
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $students
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
