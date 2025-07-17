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

$screen_id = isset($_POST['screen_id']) ? intval($_POST['screen_id']) : 0;
$show_promotions = isset($_POST['show_promotions']) ? intval($_POST['show_promotions']) : 0;
$promotions_days = isset($_POST['promotions_days']) ? intval($_POST['promotions_days']) : 7;
$promotions_gender = isset($_POST['promotions_gender']) ? $_POST['promotions_gender'] : '0';
$show_birthdays = isset($_POST['show_birthdays']) ? intval($_POST['show_birthdays']) : 0;
$birthdays_days = isset($_POST['birthdays_days']) ? intval($_POST['birthdays_days']) : 7;
$birthdays_gender = isset($_POST['birthdays_gender']) ? $_POST['birthdays_gender'] : '0';

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

// Validate that the screen belongs to one of the user's schools
$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();
$school_ids = array_keys($schools);

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
    // Insert or update settings using ON DUPLICATE KEY UPDATE
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO screen_settings (screen_id, show_promotions, promotions_days, promotions_gender, show_birthdays, birthdays_days, birthdays_gender) 
        VALUES (?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            show_promotions = VALUES(show_promotions),
            promotions_days = VALUES(promotions_days),
            promotions_gender = VALUES(promotions_gender),
            show_birthdays = VALUES(show_birthdays),
            birthdays_days = VALUES(birthdays_days),
            birthdays_gender = VALUES(birthdays_gender)
    ");

    $result = $stmt->execute([
        $screen_id, 
        $show_promotions, 
        $promotions_days, 
        $promotions_gender,
        $show_birthdays, 
        $birthdays_days,
        $birthdays_gender
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save settings']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 