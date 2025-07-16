<?php
$dir = __DIR__ . '/images/';
$school_id = isset($_POST['school_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['school_id']) : '';
if (!$school_id) die('');
$meta_file = $dir . 'metadata_' . $school_id . '.json';
$meta = [];
if (file_exists($meta_file)) {
    $meta = json_decode(file_get_contents($meta_file), true);
    if (!is_array($meta)) $meta = [];
}
// Find filename by id (md5 of filename)
function find_filename_by_id($id, $meta) {
    foreach ($meta as $fname => $props) {
        if (md5($fname) === $id) return $fname;
    }
    return false;
}
$id = $_POST['id'] ?? '';
if (!$id) die('');
$fname = find_filename_by_id($id, $meta);
if (!$fname) die('');
$filepath = $dir . $fname;
if (file_exists($filepath)) unlink($filepath);
unset($meta[$fname]);
file_put_contents($meta_file, json_encode($meta));
echo 'ok'; 