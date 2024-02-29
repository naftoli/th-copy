<?php
ini_set('display_errors',1);
ini_set('error_reporting', 1);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';  
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        join schools s on u.school_id = s.school_id 
        join classes c on c.class_id = u.class_id 
        left join thumbs t on u.user_photo_id = t.file_id 
        where year = " . $year . " 
        and date_paid > 0 
        and tc.school_id in (" . implode(',', array_keys( $schools )) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['school_id']][] = $row;
}
// echo "<pre>"; print_r( $info ); echo "</pre>"; exit;

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

function custom_urlencode($url) {
    return implode('/', array_map('rawurlencode', explode('/', $url)));
}

$imgs = []; // array for keeping track of all pictures that are showing up
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Review Enrollment</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
            .pics img {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                border-color: #aaa;
                margin-left: auto;
                margin-right: auto;
                display: block;
            }
        </style>
    </HEAD>

    <BODY>
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Chidon Pictures</h1>
<!--        <a href="chidon_pics_download.php" target="__blank"><button id="downloadPics">Download Pictures</button></a>-->
        <a href="chidon_pics_iyun.php" target="__blank"><button id="downloadPicsIyun">Download Iyun Only Pictures</button></a>
        <?php foreach ( $info as $id => $children ) : ?>
            <h2><?= $schools[$id] ?></h2>
            <table class="pics">
                <tr>
                    <th>Serial Number</th>
                    <th>Name</th>
                    <th>School</th>
                    <th>Grade</th>
                    <th>Chidon Picture</th>
                </tr>
                <?php
                foreach ( $children as $child ) {
                    $grade = $child['class_grade'] . ($child['class_sub'] ? '-' . $child['class_sub'] : '');
                    $img_fallbacks = [
                        ['val' => $child['mobile_pic'],     'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['mobile_pic'])],
                        ['val' => $child['thumb'],          'url' => 'https://mashpia.com/mobile/reg/thumbs/' . custom_urlencode($child['thumb'])],
                        ['val' => $child['user_photo_id'],  'url' => 'https://mashpia.com/file_view.php?id=' . $child['user_photo_id']],
                        ['val' => true,                     'url' => 'https://mashpia.com/mobile/reg/img/addphoto.png']
                    ];
                    $img = null;
                    // find first valid image
                    foreach($img_fallbacks as $img_fallback) {
                        if ( !empty($img_fallback['val']) && $img_fallback['val'] !== 'img/addphoto.png' ) {
                            $img = $img_fallback['url'];
                            break;
                        }
                    }
                    echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $child['first'] . ' ' . $child['last'] . "</td><td>";
                    echo $child['school_name'] . "</td><td>" . $grade . "</td><td>";
                    echo "<img src='" . $img . "' /></td></tr>";
                    if ($img != 'http://mashpia.com/mobile/reg/img/addphoto.png') {
                        $imgs[] = $img;
                    }
                }
                ?>
            </table>
        <?php endforeach; ?> 
    </BODY>
</HTML>