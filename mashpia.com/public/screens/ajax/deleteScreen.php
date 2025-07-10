<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.adminSchools.php';

$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();

$school_id = $_POST['school_id'];
$screen_url = $_POST['screen_url'];

// Validate access - ensure the school can only delete their own screens if not super admin
if ($admin_user['auth'] !== 'super' && !in_array($school_id, array_keys($schools))) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    // Delete screen
    $delete_sql = "DELETE FROM screens WHERE url = ? AND school_id = ?";
    $delete_stmt = $MASHPIA_DB->prepare($delete_sql);
    $result = $delete_stmt->execute([$screen_url, $school_id]);
    
    if ($result && $delete_stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Screen not found or already deleted']);
    }
} catch (Exception $e) {
    error_log("Delete error in deleteScreen.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 