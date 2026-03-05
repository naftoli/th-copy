<?php
// Proxy image through same origin so canvas.toDataURL() is not tainted by cross-origin.
$allowedPrefixes = ['chidonOld/certs/Jpegs/', 'mobile/reg/'];
$path = isset($_GET['path']) ? trim($_GET['path'], '/') : '';

$ok = false;
foreach ($allowedPrefixes as $prefix) {
    if (strpos($path, $prefix) === 0) {
        $ok = true;
        break;
    }
}
if (!$ok || strpos($path, '..') !== false) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
$contentType = isset($types[$ext]) ? $types[$ext] : 'application/octet-stream';

$isRemote = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'tzivos.local'], true);
if ($isRemote) {
    $url = 'https://mashpia.com/' . $path;
    $data = @file_get_contents($url);
    if ($data === false) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    header('Content-Type: ' . $contentType);
    header('Cache-Control: private, max-age=3600');
    echo $data;
} else {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
    if (!is_file($fullPath) || !is_readable($fullPath)) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    header('Content-Type: ' . $contentType);
    header('Cache-Control: private, max-age=3600');
    readfile($fullPath);
}
