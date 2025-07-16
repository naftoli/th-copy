<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/images/';
$screen_id = isset($_REQUEST['screen_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['screen_id']) : '';
$school_id = isset($_REQUEST['school_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['school_id']) : '';
if (!$school_id) {
    echo json_encode([]);
    exit;
}
$meta_file = $dir . 'metadata_' . $school_id . '.json';
// Load metadata
$meta = [];
if (file_exists($meta_file)) {
    $meta = json_decode(file_get_contents($meta_file), true);
    if (!is_array($meta)) $meta = [];
}
$images = [];
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if (in_array($file, ['.', '..']) || strpos($file, 'metadata_') === 0) continue;
        $path = $dir . $file;
        if (is_file($path) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            // Only include if screen_id matches (if provided)
            if ($screen_id && (!isset($meta[$file]['screen_id']) || $meta[$file]['screen_id'] !== $screen_id)) continue;
            $id = md5($file);
            $images[] = [
                'id' => $id,
                'filename' => $file,
                'url' => 'images/' . $file,
                'size' => isset($meta[$file]['size']) ? $meta[$file]['size'] : 150,
                'visible' => isset($meta[$file]['visible']) ? !!$meta[$file]['visible'] : false
            ];
        }
    }
}
echo json_encode($images); 