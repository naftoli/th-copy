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

function createFile($name, $info) {
    $fp = fopen($name, "w");
    fputs($fp);
    fputs($fp, $info);
    fclose($fp);
}

function createZip($files, $filename) {
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file) {
        $zip->addFromString($file, file_get_contents($file));
        unlink($file);
    }
    $zip->close();
}

$info = json_decode(file_get_contents('php://input'), true);
foreach ($info as $idx => $details) createFile('imgs/' . ($idx + 1) . "_" . str_replace(' ', '_', $details['name']), $details['src'])                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              ;

// loop through dir to get files and create zip file
$dir = getcwd();
$list = scandir($dir . '/imgs');
$images = extractFiles($list);
$filename = "Generals.zip";
createZip($images, $filename);

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