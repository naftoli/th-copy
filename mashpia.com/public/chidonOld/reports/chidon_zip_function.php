<?php
function custom_urlencode($url) {
    return implode('/', array_map('rawurlencode', explode('/', $url)));
}

function createZip($files, $filename) {
    // $image_extensions = explode(',', "jpg,jpeg,jpe,jif,jfif,jfi,png,gif,webp,tiff,tif,raw,arw,cr2,nrw,k25,bmp,dib,heif,heic,jp2,j2k,jpf,jpx,jpm,mj2,svg,svgz");
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file_with_fallbacks) {
        $filename = $file_with_fallbacks['filename'];
        $fallbacks = $file_with_fallbacks['fallbacks'];
        foreach($fallbacks as $file) {
            if ($file['from_db']) {
                $sql = "SELECT file_name, file_data FROM files WHERE file_id = '{$file['val']}'";
                $query = mysql_query($sql);
                if (!$query) break;
                $row = mysql_fetch_assoc($query);
                $file_contents = $row['file_data'];
                $file_name_split = explode('.', $row['file_name']);
                $file_name = $file_name_split[0];
            } else {
                $file_contents = @file_get_contents($file['url']);
                $url_split = explode('.', $file['url']);
                $file_name = $url_split[0];
            }
            if ($file_contents && $png_file = imagecreatefromstring($file_contents)) {
                // create a png file from the file_contents and add it to the zip
                $file_name = $file_name . '.png';
                imagepng($png_file, $file_name);
                imagedestroy($png_file);
                $zip->addFromString($file_name, file_get_contents($file_name));
                unlink($file_name);
                break;
            }
        }
    }
    $zip->close();

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
}