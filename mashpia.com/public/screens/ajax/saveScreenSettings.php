<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.adminSchools.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get all the data from the form
$screen_id = isset($_POST['screen_id']) ? intval($_POST['screen_id']) : 0;
$school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
$screen_url = isset($_POST['screen_url']) ? $_POST['screen_url'] : '';
$new_url = isset($_POST['new_url']) ? $_POST['new_url'] : '';
$screen_name = isset($_POST['screen_name']) ? $_POST['screen_name'] : '';
$screen_size = isset($_POST['screen_size']) ? $_POST['screen_size'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Settings data
$show_promotions = isset($_POST['show_promotions']) ? intval($_POST['show_promotions']) : 0;
$promotions_days = isset($_POST['promotions_days']) ? intval($_POST['promotions_days']) : 7;
$promotions_gender = isset($_POST['promotions_gender']) ? $_POST['promotions_gender'] : '0';
$show_birthdays = isset($_POST['show_birthdays']) ? intval($_POST['show_birthdays']) : 0;
$birthdays_days = isset($_POST['birthdays_days']) ? intval($_POST['birthdays_days']) : 7;
$birthdays_gender = isset($_POST['birthdays_gender']) ? $_POST['birthdays_gender'] : '0';
$show_chidon = isset($_POST['show_chidon']) ? intval($_POST['show_chidon']) : 0;
$show_chayolei = isset($_POST['show_chayolei']) ? intval($_POST['show_chayolei']) : 0;

// Validate inputs
if (!$screen_id || !is_numeric($promotions_days) || !is_numeric($birthdays_days)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Validate gender values
if (!in_array($promotions_gender, ['0', 'M', 'F'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid promotions gender value']);
    exit;
}

if (!in_array($birthdays_gender, ['0', 'M', 'F'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid birthdays gender value']);
    exit;
}

// Ensure days are within valid range
$promotions_days = max(1, min(90, $promotions_days));
$birthdays_days = max(1, min(90, $birthdays_days));

// Validate password is provided
if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password is required']);
    exit;
}

// Store password as plain text (no hashing)
$plain_password = $password;

// Validate that the screen belongs to one of the user's schools
$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();
$school_ids = array_keys($schools);

// Validate access - ensure the school can only edit their own screens if not super admin
if ($admin_user['auth'] !== 'super' && !in_array($school_id, array_keys($schools))) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if screen exists and belongs to user's schools
$stmt = $MASHPIA_DB->prepare("
    SELECT screen_id FROM screens 
    WHERE screen_id = ? AND school_id IN (" . implode(',', $school_ids) . ")
");
$stmt->execute([$screen_id]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Screen not found or access denied']);
    exit;
}

try {
    // Start transaction
    $MASHPIA_DB->beginTransaction();
    
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
    
    // Update screen details
    $update_sql = "UPDATE screens SET screen_name = ?, screen_size = ?, url = ?, password = ? WHERE screen_id = ? AND school_id = ?";
    $update_stmt = $MASHPIA_DB->prepare($update_sql);
    $screen_result = $update_stmt->execute([$screen_name, $screen_size, $new_url, $plain_password, $screen_id, $school_id]);
    
    if (!$screen_result) {
        throw new Exception('Failed to update screen details');
    }
    
    // Insert or update settings using ON DUPLICATE KEY UPDATE
    $settings_stmt = $MASHPIA_DB->prepare("
        INSERT INTO screen_settings (screen_id, show_promotions, promotions_days, promotions_gender, show_birthdays, birthdays_days, birthdays_gender, show_chidon, show_chayolei) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            show_promotions = VALUES(show_promotions),
            promotions_days = VALUES(promotions_days),
            promotions_gender = VALUES(promotions_gender),
            show_birthdays = VALUES(show_birthdays),
            birthdays_days = VALUES(birthdays_days),
            birthdays_gender = VALUES(birthdays_gender),
            show_chidon = VALUES(show_chidon),
            show_chayolei = VALUES(show_chayolei)
    ");

    $settings_result = $settings_stmt->execute([
        $screen_id, 
        $show_promotions, 
        $promotions_days, 
        $promotions_gender,
        $show_birthdays, 
        $birthdays_days,
        $birthdays_gender,
        $show_chidon,
        $show_chayolei
    ]);

    if (!$settings_result) {
        throw new Exception('Failed to save settings');
    }
    
    // Commit transaction
    $MASHPIA_DB->commit();
    
    echo json_encode(['success' => true, 'message' => 'Screen and settings updated successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $MASHPIA_DB->rollBack();
    error_log("Error in saveScreenSettings.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 