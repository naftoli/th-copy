<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER["DOCUMENT_ROOT"] . '/header.php';

// make sure only super admins can access
if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

function createZip($files, $filename) {
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach ($files as $idx => $file) {
        $file_name = ($idx + 1) . str_replace(' ', '_', $file['name']);
        $zip->addFromString($file_name, file_get_contents('http:' . $file['src']));
    }
    $zip->close();
}

$info = json_decode(file_get_contents('php://input'), true);
$filename = "Generals.zip";
createZip($info, $filename);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filename));
flush(); // Flush system output buffer
readfile($filename);
unlink($filename);