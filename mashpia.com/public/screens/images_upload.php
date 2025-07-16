<?php
$dir = __DIR__ . '/images/';
$school_id = isset($_POST['school_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['school_id']) : '';
if (!$school_id) {
    echo json_encode(['success' => false, 'message' => 'Missing school_id']);
    exit;
}
$meta_file = $dir . 'metadata_' . $school_id . '.json';
if (!is_dir($dir)) mkdir($dir, 0777, true);
// Load metadata
$meta = [];
if (file_exists($meta_file)) {
    $meta = json_decode(file_get_contents($meta_file), true);
    if (!is_array($meta)) $meta = [];
}
$screen_id = isset($_POST['screen_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['screen_id']) : '';
if (!$screen_id) {
    echo json_encode(['success' => false, 'message' => 'Missing screen_id']);
    exit;
}
$allowed = ['jpg','jpeg','png','gif','webp'];
$success = false;
$uploaded = [];
if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['name'] as $i => $name) {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;
        $tmp = $_FILES['images']['tmp_name'][$i];
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($name, PATHINFO_FILENAME));
        $newname = $screen_id . '_' . uniqid('img_') . '_' . $safeName . '.' . $ext;
        $dest = $dir . $newname;
        if (move_uploaded_file($tmp, $dest)) {
            $meta[$newname] = [
                'size' => 150,
                'visible' => false,
                'screen_id' => $screen_id
            ];
            $success = true;
            $uploaded[] = $newname;
        }
    }
    file_put_contents($meta_file, json_encode($meta));
}
echo json_encode(['success' => $success, 'uploaded' => $uploaded]); 