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

if (empty($school_id)) {
    echo json_encode(['success' => false, 'message' => 'School ID is required']);
    exit;
}

if (!is_numeric($school_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid school ID']);
    exit;
}

try {
    // Get grades for the specified school
    $stmt = $MASHPIA_DB->prepare("
        SELECT class_id, class_grade, class_sub  
        FROM classes  
        WHERE school_id = :school_id 
        AND class_era = 0
        ORDER BY class_grade, class_sub
    ");
    
    $stmt->execute([':school_id' => $school_id]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $grades
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
