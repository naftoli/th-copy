<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

function passedKHK($child) {
    global $khk_marks;
    if (isset($khk_marks[$child['th_chidon_id']])) {
        $user_marks = $khk_marks[$child['th_chidon_id']];
        $total = 0;
        foreach ($user_marks as $mark) $total += intval($mark);
        $total /= 4;
        if ($total >= 70) return true;
    }
    return false;
}

function passedKHKFinal($child) {
    $needed = 140;
    $marks = getFinalMarks();
    if (isset($marks[$child['user_id']])) {
        $total = 0;
        foreach ($marks[$child['user_id']] as $mark) $total += intval($mark);
        if ($total >= $needed) return true;
    }
    return false;
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

$type = isset($_GET['type']) ? $_GET['type'] : 'finals';

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
        and c.class_grade = '8' 
        and tc.date_paid > 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    if ($type == 'finals') {
        if (passedKHKFinal($row)) $info[$row['school_id']][] = $row;
    } else {
        if (passedKHK($row)) $info[$row['school_id']][] = $row;
    }
}

require_once 'chidon_zip_function.php';
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
