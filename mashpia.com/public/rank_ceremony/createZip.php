<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

function extractFiles($list) {
    $files = [];
    foreach ($list as $name) {
        if (is_dir($name)) continue;
        if ($name === '.' || $name === '..' || strpos($name, '.php') !== false) continue;
        else $files[] = $name;
    }
    return $files;
}

function createZip($files, $images, $filename) {
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file) {
        $zip->addFromString($file, file_get_contents($file));
//        unlink($file);
    }
    foreach ($images as $img) {
        $img = 'images/' . $img;
        $zip->addFromString($img, file_get_contents($img));
//        unlink($img);
    }
    $zip->close();
}

// loop through dir to get files
$dir = getcwd();
$list = scandir($dir);
$list2 = scandir($dir . '/images');
$files = extractFiles($list);
$images = extractFiles($list2);

$filename = "Data.zip";
createZip($files, $images, $filename);

//header('Content-Description: File Transfer');
//header('Content-Type: application/octet-stream');
//header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
//header('Expires: 0');
//header('Cache-Control: must-revalidate');
//header('Pragma: public');
//header('Content-Length: ' . filesize($filename));
//flush(); // Flush system output buffer
//readfile($filename);
//unlink($filename);