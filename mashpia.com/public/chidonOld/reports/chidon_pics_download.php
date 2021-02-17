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
    foreach($files as $file) {
        $zip->addFromString($file, file_get_contents($file));
    }
    $zip->close();
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
        $img = 'http://mashpia.com/mobile/reg/img/addphoto.png';
        if (!empty($child['chidon_pic'])) {
            $img = 'http://mashpia.com/mobile/reg/' . $child['chidon_pic'];
        } else if (!empty($child['mobile_pic'])) {
            $img = 'http://mashpia.com/mobile/reg/' . $child['mobile_pic'];
        } else if (
            !empty($child['thumb'])
            && file_exists('http://mashpia.com/mobile/reg/thumbs/' . $child['thumb'])
        ) {
            $img = 'http://mashpia.com/mobile/reg/thumbs/' . $child['thumb'];
        } else if (!empty($child['user_photo_id'])) {
            $img = 'http://mashpia.com/file_view.php?id=' . $child['user_photo_id'];
        }
        if ($img != 'http://mashpia.com/mobile/reg/img/addphoto.png') {
            $imgs[] = $img;
        }
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