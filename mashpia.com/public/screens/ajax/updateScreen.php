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
$new_url = $_POST['new_url'];
$screen_name = $_POST['screen_name'];
$screen_size = $_POST['screen_size'];
$password = $_POST['password'] ?? '';

// Validate password is provided
if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password is required']);
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Validate access - ensure the school can only edit their own screens if not super admin
if ($admin_user['auth'] !== 'super' && !in_array($school_id, array_keys($schools))) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    // Check if new URL already exists (if URL is being changed)
    if ($new_url !== $screen_url) {
        $existing_url = $MASHPIA_DB->prepare("SELECT url FROM screens WHERE url = ? AND school_id = ? AND url != ?");
        $existing_url->execute([$new_url, $school_id, $screen_url]);
        $res = $existing_url->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            echo json_encode(['success' => false, 'message' => 'URL already in use']);
            exit;
        }
    }
    
    // Update screen
    $update_sql = "UPDATE screens SET screen_name = ?, screen_size = ?, url = ?, password = ? WHERE url = ? AND school_id = ?";
    $update_stmt = $MASHPIA_DB->prepare($update_sql);
    $result = $update_stmt->execute([$screen_name, $screen_size, $new_url, $hashed_password, $screen_url, $school_id]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update screen']);
    }
} catch (Exception $e) {
    error_log("Update error in updateScreen.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 