<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/images/';
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$school_id = isset($data['school_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $data['school_id']) : '';
if (!$school_id) {
    echo json_encode(['success' => false, 'message' => 'Missing school_id']);
    exit;
}
$meta_file = $dir . 'metadata_' . $school_id . '.json';
if (!file_exists($meta_file)) {
    echo json_encode(['success' => false, 'message' => 'No metadata file']);
    exit;
}
$meta = json_decode(file_get_contents($meta_file), true);
if (!is_array($meta)) $meta = [];
if (!isset($data['updates']) || !is_array($data['updates'])) {
    echo json_encode(['success' => false, 'message' => 'No updates provided']);
    exit;
}
// Helper to find filename by id
function find_filename_by_id($id, $meta) {
    foreach ($meta as $fname => $props) {
        if (md5($fname) === $id) return $fname;
    }
    return false;
}
foreach ($data['updates'] as $update) {
    $id = $update['id'] ?? '';
    if (!$id) continue;
    $fname = find_filename_by_id($id, $meta);
    if (!$fname) continue;
    if (isset($update['size'])) {
        $size = intval($update['size']);
        if ($size === 0 || $size > 0) {
            $meta[$fname]['size'] = $size;
        }
    }
    if (isset($update['visible'])) {
        $meta[$fname]['visible'] = !!$update['visible'];
    }
}
file_put_contents($meta_file, json_encode($meta));
echo json_encode(['success' => true]); 