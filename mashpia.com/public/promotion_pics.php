<?php
$admin_auth = ['school'];
require_once 'header.php';
require_once 'class.rankReport.php';
$r = new RankReport;
$r->setRanks('byRankFirst');
$ranks = $r->getRanks();
$logos = $r->getSchoolLogos();
//echo "<pre>"; print_r( $ranks ); echo "</pre>";
function getPic( $user_id ) {
    $pic = [];
    $sql = "select u.mobile_pic, u.user_photo_id, thumb from users u 
            left join thumbs t on t.file_id = u.user_photo_id 
            where u.user_id = " . $user_id;
    $result = mysql_query( $sql );
    if ( mysql_num_rows($result) > 0 ) {
        $pic = mysql_fetch_assoc($result);
    }
    return $pic;
}

// vars to indicate how many rows and columns should show
define('ROWS', 11);
define('COLS', 20);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            html {
                /* background-color: #0a0044; */
            }
            .rank {
                top: 77px;
                width: 3657px;
                border: 1px solid black;
                height: 250px;
                margin-bottom: 10px;
                color: #fff;
                font-size: 72px;
            }
            .school {
                width: 360px;
                height: 360px;
            }
            .user {
                width: 177px;
                height: 177px;
            }
            img {
                margin-right: 6px;
                margin-bottom: 6px;
            }
            /* tr, td {
                border: 1px solid black;
                width: 177px;
                height: 177px;
            } */
            .col {
                display: flex;
                flex-direction: column;
            }
            .row {
                display: flex;
                flex-direction: row;
            }
            hr {
                padding-top: 100px;
                padding-bottom: 100px;
            }
        </style>
    </head>
    <body>
        <?php 
        $genders = ['M','F'];
        foreach ( $genders as $gender ) {
            echo "<h1>" . ($gender == 'M' ? 'BOYS' : 'GIRLS') . "</h1>";
            foreach ( $ranks[$gender] as $rank => $info ) {
                if ( $rank == 'General' ) break;

                // vars to keep track of how many cols and rows there are
                $cols = 1;
                $rows = 0;

                $newColumn = "</div><div class='col'>";
                $newRow = "</div></div></div><div class='col'><div class='row'><div class='col'>";
                $newBox = "</div></div></div></div><div style='page-break-after: always'></div><div class='rank'>" . $rank . "</div><div class='row'><div class='col'>";

                echo "<div class='rank'>" . $rank . "</div>";
                echo "<div style='page-break-after: always;'></div>";
                echo "<div class='row'>";
                echo "<div class='col'>";
                foreach ( $info as $school => $users ) {                    
                    include 'promotion_pics_main.php';
                }
                echo "</div></div>";
            }
        }
        ?>
    </body>
</html>