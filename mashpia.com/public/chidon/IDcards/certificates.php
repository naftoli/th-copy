<?php
ini_set('display_errors', 1);
require __DIR__ . '/../../db.php';

$info = array();
$sql = "select u.first_he, u.last_he, tc.grade, tc.cert_number from users u
        join th_chidon tc using (user_id)
        where tc.year = 5779
        and tc.paid > 0 
        and u.gender = 'F'
        order by tc.cert_number";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8">
        <style>
            body {
                margin : 0;
            }
            @font-face {
                font-family: tramp;
                src: url('fonts/FiraCode-Regular.otf');
            }
            @font-face {
                font-family: goth;
                src: url('fonts/GOTHAM-MEDIUM.OTF');
            }
            img {
                width: 9in;
                height: 11.6in;
            }
            .name {
                top: 580px;
                position: absolute; 
                width: 865px;
                color: #f59900;
                font-family: tramp;
                font-size: 42pt; 
                text-align: center;
                line-height: 35pt;
            }
            .image { 
                position: relative; 
                width: 100%; /* for IE 6 */
             }
             .certNum {
                position: absolute;
                color: #000;
                top: 10.8in;
                left: 0.5in;
                font-size: 11px;
                font-family: goth;
             }
        </style>
    </head>
    <body>
        <?php
        foreach ($info as $row) {
            $grade = $row['grade'];
            $name = $row['first_he'] . "<br />" . $row['last_he'];
            ?>
            <div class="image">
                <img src="new\background\cert-<?=$grade?>-g.png" />
                <div class="name">
                    <?=$name?>
                </div>
                <div class="certNum"><?=$row['cert_number']?></div>
            </div>
            <div style="clear: both"></div>
        <?php } ?>
    </body>
</html>