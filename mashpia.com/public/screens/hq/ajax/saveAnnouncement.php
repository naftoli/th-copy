<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../api/header/db.php';
header('Content-Type: application/json');

function respond($success, $message = '', $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// make sure it's a super admin
if ($admin_user['auth'] != 'super') {
    respond(false, 'You are not authorized to do this.');
    exit;
}

// Validate required fields
$text = trim($_POST['announcement_text'] ?? '');
$image_size = $_POST['announcement_image_size'] ?? '';
$text_size = $_POST['announcement_text_size'] ?? '';
$type = $_POST['announcement_type'] ?? '';
$from_date = $_POST['from_date'] ?? '';
$to_date = $_POST['to_date'] ?? '';
$limit_to_schools = isset($_POST['limit_school']) ? $_POST['limit_school'] : [];
$limit_to_classes = isset($_POST['limit_class']) ? $_POST['limit_class'] : [];
$created_by_hq = 1;
$screen_announcement_id = $_POST['announcement_id'] ?? null;

// Validate type
if (empty($type) || !in_array($type, ['chidon', 'chayolei'])) {
    respond(false, 'Please select a valid type (Chidon or Chayolei).');
}

if ($text === '' && empty($_FILES['announcement_image']['name'])) {
    respond(false, 'Please provide announcement text or upload an image.');
}

// Validate dates
if (empty($from_date) || empty($to_date)) {
    respond(false, 'Please provide both from date and to date.');
}

if ($from_date > $to_date) {
    respond(false, 'From date cannot be after to date.');
}

// Handle image upload if present
$image_url = '';
if (!empty($_FILES['announcement_image']['name'])) {
    $upload_dir = '/screens/hq/images/';
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . $upload_dir;
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $filename = uniqid('announcement_') . '_' . basename($_FILES['announcement_image']['name']);
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES['announcement_image']['tmp_name'], $target_file)) {
        $image_url = $upload_dir . $filename;
    } else {
        respond(false, 'Failed to upload image.');
    }
} elseif (isset($_POST['existing_image_url'])) {
    $image_url = $_POST['existing_image_url'];
} elseif (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
    $image_url = ''; // Remove the image
}

// Prepare data
// Handle empty values properly - if array contains only empty strings, treat as empty
$schools_str = '';
if (is_array($limit_to_schools) && !empty($limit_to_schools)) {
    $filtered_schools = array_filter($limit_to_schools, function($val) { return $val !== ''; });
    $schools_str = !empty($filtered_schools) ? implode(',', $filtered_schools) : '';
}

$classes_str = '';
if (is_array($limit_to_classes) && !empty($limit_to_classes)) {
    $filtered_classes = array_filter($limit_to_classes, function($val) { return $val !== ''; });
    $classes_str = !empty($filtered_classes) ? implode(',', $filtered_classes) : '';
}

// Insert or update
try {
    if ($screen_announcement_id) {
        // Update
        $stmt = $MASHPIA_DB->prepare("UPDATE screen_announcements SET text=?, image=?, image_size=?, text_size=?, type=?, from_date=?, to_date=?, limit_to_schools=?, limit_to_classes=? WHERE screen_announcement_id=?");
        $stmt->execute([
            $text,
            $image_url,
            $image_size,
            $text_size,
            $type,
            $from_date,
            $to_date,
            $schools_str,
            $classes_str,
            $screen_announcement_id
        ]);
        respond(true, 'Announcement updated successfully.');
    } else {
        // Insert
        $stmt = $MASHPIA_DB->prepare("INSERT INTO screen_announcements (created_by_hq, text, image, image_size, text_size, type, from_date, to_date, limit_to_schools, limit_to_classes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $created_by_hq,
            $text,
            $image_url,
            $image_size,
            $text_size,
            $type,
            $from_date,
            $to_date,
            $schools_str,
            $classes_str
        ]);
        respond(true, 'Announcement created successfully.');
    }
} catch (Exception $e) {
    respond(false, 'Database error: ' . $e->getMessage());
} 