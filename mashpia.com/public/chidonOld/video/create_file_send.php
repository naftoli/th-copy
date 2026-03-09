<?php
/**
 * Sends the zip built by create_file.php. No header.php = no output before the file (avoids corrupt zip).
 */
session_start();

$token = isset($_GET['t']) ? preg_replace('/[^a-f0-9]/', '', $_GET['t']) : '';
if (strlen($token) !== 32 || !isset($_SESSION['video_download_token']) || $_SESSION['video_download_token'] !== $token) {
    header('HTTP/1.0 403 Forbidden');
    exit('Invalid or expired download.');
}

// Expire after 2 minutes
if (isset($_SESSION['video_download_time']) && (time() - $_SESSION['video_download_time']) > 120) {
    unset($_SESSION['video_download_token'], $_SESSION['video_download_time']);
    header('HTTP/1.0 404 Not Found');
    exit('Download link expired.');
}

$dir = __DIR__;
$zipPath = $dir . DIRECTORY_SEPARATOR . 'ChidonVideo_' . $token . '.zip';

if (!is_file($zipPath) || !is_readable($zipPath)) {
    unset($_SESSION['video_download_token'], $_SESSION['video_download_time']);
    header('HTTP/1.0 404 Not Found');
    exit('File not found.');
}

unset($_SESSION['video_download_token'], $_SESSION['video_download_time']);

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="ChidonVideo.zip"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);
unlink($zipPath);
exit;
