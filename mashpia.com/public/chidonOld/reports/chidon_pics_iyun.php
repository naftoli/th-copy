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

function getFinalMarks() {
    global $year;

    $marks = [];
    $sql = "select * from th_chidon_finals where year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $marks[$row['user_id']] = $row;
    }
    return $marks;
}

function getAward($child) {
    global $final_marks;

    $tracks = [
        1   => 'yesod',
        2   => 'yediah',
        3   => 'havonah',
        4   => 'iyun'
    ];
    $finals = [
        'yesod'     => 20,
        'yediah'    => 40,
        'havonah'   => 60,
        'iyun'      => 80
    ];
    $needed = [
        'yesod'     => 60,
        'yediah'    => 70,
        'havonah'   => 80,
        'iyun'      => 90
    ];
    $awards = [
        'yesod'     => 'certificate',
        'yediah'    => 'plaque',
        'havonah'   => 'medal / plaque',
        'iyun'      => 'trophy / medal / plaque'
    ];

    $highest_track = $child['highest_track'];
    // find out if award is same as before final or not
    $award = false;
    $key = array_search($highest_track, $tracks);
    if ($key !== false) {
        // go down from key to find where the child is holding
        if (isset($final_marks[$child['user_id']])) {
            $row = $final_marks[$child['user_id']];
            $score = 0;
            for ($i = 1; $i <= $key; $i++) {
                $level = 'level_' . $i;
                if ($row[$level]) {
                    $score += $row[$level];
                }
            }
            for ($i = 1; $i <= $key; $i++) {
                $divide_by = $finals[$tracks[$i]];
                $final_score = number_format(($score / $divide_by) * 100, 2);
                if ($final_score >= $needed[$tracks[$i]]) {
                    $award = $tracks[$i];
                }
            }
        }
    }
    if ($award === 'iyun') return true;
    else return false;
}

function custom_urlencode($url) {
    return implode('/', array_map('rawurlencode', explode('/', $url)));
}

$final_marks = getFinalMarks();

$info = [];
$sql = "select * from th_chidon tc 
        join th_chidon_info tci using (user_id, year) 
        join users u using (user_id) 
        left join thumbs t on u.user_photo_id = t.file_id 
        where year = " . $year . " and tc.school_id in (" . implode(',', array_keys( $schools )) . ") 
        and highest_track = 'iyun'";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    if (getAward($row)) $info[$row['school_id']][] = $row;
}

$imgs = []; // array for keeping track of all pictures that are showing up
foreach ( $info as $id => $children ) {
    foreach ($children as $child) {
        $img_fallbacks = [
            ['from_db' => false, 'val' => $child['chidon_pic_5782'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5782'])],
            ['from_db' => false, 'val' => $child['chidon_pic_5781'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5781'])],
            ['from_db' => false, 'val' => $child['mobile_pic'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['mobile_pic'])],
            ['from_db' => false, 'val' => $child['thumb'],         'url' => 'https://mashpia.com/mobile/reg/thumbs/' . custom_urlencode($child['thumb'])],
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

$filename = 'chidonPicsIyun.zip';
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