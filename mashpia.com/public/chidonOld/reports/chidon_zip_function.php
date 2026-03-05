<?php
function custom_urlencode($url) {
    return implode('/', array_map('rawurlencode', explode('/', $url)));
}

function createZip($files, $zip_path) {
    $zip = new ZipArchive;
    $success = $zip->open($zip_path, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$zip_path>\n");
    }
    foreach ($files as $file) {
        $entry_name = $file['filename'];
        $fallbacks = $file['fallbacks'];
        $img = null;
        foreach ($fallbacks as $img_fallback) {
            if (!empty($img_fallback['val']) && $img_fallback['val'] !== 'img/addphoto.png') {
                $img = $img_fallback['url'];
                break;
            }
            if ($img) {
                $file_contents = @file_get_contents($img);
                if ($file_contents && $png_img = @imagecreatefromstring($file_contents)) {
                    ob_start();
                    @imagepng($png_img);
                    $png_data = ob_get_clean();
                    @imagedestroy($png_img);
                    $zip->addFromString($entry_name . '.png', $png_data);
                    break;
                }
            }
        }
    }
    $zip->close();

    // download the zip file
    downloadZip($zip_path);
}

function downloadZip($zip_path) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zip_path));
    flush();
    readfile($zip_path);
    unlink($zip_path);
}