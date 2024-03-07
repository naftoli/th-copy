<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

function createZip($files, $filename) {
    $image_extensions = explode(',', "jpg,jpeg,jpe,jif,jfif,jfi,png,gif,webp,tiff,tif,raw,arw,cr2,nrw,k25,bmp,dib,heif,heic,jp2,j2k,jpf,jpx,jpm,mj2,svg,svgz");
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
                $origanal_extension = end($file_name_split);
            } else {
                $file_contents = @file_get_contents($file['url']);
                $url_split = explode('.', $file['url']);
                $origanal_extension = end($url_split);
            }
            if ($file_contents) {
                $extension = in_array($origanal_extension, $image_extensions) ? $origanal_extension : "jpg";
                // for debugging without zip extension
                // echo '<img width="100px" src="data:image/png;base64, ' . base64_encode($file_contents) . '">';
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

function eligibleForKhk($child) {
    global $khk_marks;
    if (isset($khk_marks[$child['th_chidon_id']]) && $child['date_paid'] > 0) {
        $user_marks = $khk_marks[$child]['th_chidon_id'];
        $total = 0;
        foreach ($user_marks as $mark) $total += intval($mark);
        $total /= 4;
        if ($total >= 70) return true;
    }
    return false;
}

$khk_marks = [];
$sql = "select * from th_khk_marks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $khk_marks[$row['th_chidon_id']][$row['test_number']] = $row['mark'];
}

$info = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        join classes c on c.class_id = u.class_id 
        where year = " . $year . " and tc.school_id in (" . implode(',', array_keys( $schools )) . ") 
        and c.class_grade = '8'";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    if (eligibleForKhk($row)) $info[$row['school_id']][] = $row;
}

$imgs = []; // array for keeping track of all pictures that are showing up
foreach ( $info as $id => $children ) {
    foreach ($children as $child) {
        $img_fallbacks = [
            ['from_db' => false, 'val' => $child['khk_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['khk_photo'])],
            ['from_db' => false, 'val' => $child['chidon_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_photo'])],
            ['from_db' => false, 'val' => $child['mobile_pic'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['mobile_pic'])],
            ['from_db' => false, 'val' => $child['chidon_pic_5782'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5782'])],
            ['from_db' => false, 'val' => $child['chidon_pic_5781'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5781'])],
            ['from_db' => true,  'val' => $child['user_photo_id']]
        ];
        // filter blank/invalid values
        $img_fallbacks = array_filter($img_fallbacks, function($img){
            return !empty($img['val']) && $img['val'] !== 'img/addphoto.png';
        });
        // map to urls,
        $imgs[] = ['filename' => $child['user_serial'], 'fallbacks' => $img_fallbacks];
    }
}

$filename = 'chidonPicsKHK.zip';
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