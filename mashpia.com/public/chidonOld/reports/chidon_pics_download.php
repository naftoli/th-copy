<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

function createZip($files, $filename) {
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file_with_fallbacks) {
        $filename = $file_with_fallbacks['filename'];
        $fallbacks = $file_with_fallbacks['fallbacks'];
        foreach($fallbacks as $file) {
            $file_contents = @file_get_contents($file);
            if ($file_contents) {
                $extension = end(explode('.', $file));
                $zip->addFromString("$filename.$extension", $file_contents);
                break;
            }
        }
    }
    $zip->close();
}

function custom_urlencode($url) {
    return implode('/', array_map('rawurlencode', explode('/', $url)));
}

$info = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        left join thumbs t on u.user_photo_id = t.file_id 
        where year = " . $year . " and tc.school_id in (" . implode(',', array_keys( $schools )) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['school_id']][] = $row;
}

$imgs = []; // array for keeping track of all pictures that are showing up
foreach ( $info as $id => $children ) {
    foreach ($children as $child) {
        $img_fallbacks = [
            ['val' => $child['chidon_pic'],     'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic'])],
            ['val' => $child['mobile_pic'],     'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['mobile_pic'])],
            ['val' => $child['thumb'],          'url' => 'https://mashpia.com/mobile/reg/thumbs/' . custom_urlencode($child['thumb'])],
            ['val' => $child['user_photo_id'],  'url' => 'https://mashpia.com/file_view.php?id=' . $child['user_photo_id']]
        ];
        // filter blank/invalid values
        $img_fallbacks = array_filter($img_fallbacks, function($img){
            return !empty($img['val']) && $img['val'] !== 'img/addphoto.png';
        });
        // map to urls,
        $imgs[] = ['filename' => $child['th_chidon_id'], 'fallbacks' => array_column($img_fallbacks, 'url')];
    }
}

$filename = 'chidonPics.zip';
createZip($imgs, $filename);
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