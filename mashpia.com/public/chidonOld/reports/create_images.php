<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);
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
        join schools s on tc.school_id = s.school_id 
        left join thumbs t on u.user_photo_id = t.file_id 
        where date_paid > 0 and year = " . $year . " and tc.school_id in (" . implode(',', array_keys( $schools )) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['school_id']][] = $row;
}
// echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
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
        <?php 
        // create images dir
        if (!is_dir("images")) mkdir("images");
        chdir("images");
        foreach ( $info as $id => $children ) {
            foreach ( $children as $child ) {
                $img = 'http://mashpia.com/mobile/reg/img/addphoto.png';
                if ( !empty($child['chidon_pic']) ) {
                    $img = 'http://mashpia.com/mobile/reg/' . $child['chidon_pic'];
                } 
                if ( $img == 'http://mashpia.com/mobile/reg/img/addphoto.png'
                    && !empty($child['mobile_pic']) 
                    ) {
                    $img = 'http://mashpia.com/mobile/reg/' . $child['mobile_pic'];
                } 
                if ( $img == 'http://mashpia.com/mobile/reg/img/addphoto.png' 
                    && !empty($child['thumb']) 
                    && file_exists('http://mashpia.com/mobile/reg/thumbs/' . $child['thumb'])
                    ) {
                    $img = 'http://mashpia.com/mobile/reg/thumbs/' . $child['thumb'];
                }
                if ( $img == 'http://mashpia.com/mobile/reg/img/addphoto.png' 
                    && !empty($child['user_photo_id']) 
                    ) {
                    $img = 'http://mashpia.com/file_view.php?id=' . $child['user_photo_id'];
                } 
                
                // create images and download to correct folder
                $new_file = $school . '/' . $child['th_chidon_id'] . '.png';
                if (!file_exists($new_file)) {
                    $school = $child['school_name'];
                    if (!is_dir($school)) {
                        mkdir($school);
                    }
                    $new_img = imagecreatefromstring(file_get_contents(urlencode($img)));
                    $new_image = imagepng($new_img, $school . '/' . $child['th_chidon_id'] . '.png');
                }
            }
        }
        chdir("../");
        ?>
        <p>
            <a href="images/">Images Directory</a>
        </p> 
        <!-- <p>
            <form action="download_images.php">
                <button>Download zipped folder</button>
            </form>
        </p> -->
    </BODY>
</HTML>