<?php
chdir('../../');
$admin_auth = ['school'];
require_once 'header.php';
require_once 'class.rankReport.php';
$r = new RankReport;
$r->setRanks('byGenerals');
$ranks = $r->getRanks();
$logos = $r->getSchoolLogos();
$userInfo = $r->getUserInfo();
$userSchool = $r->getUserSchool();
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
                background-color: #0a0044;
            }
            .rank {
                top: 77px;
                width: 3657px;
                height: 250px;
                margin-bottom: 10px;
                margin-left: auto;
                margin-right: auto;
                font-size: 72px;
            }
            .school {
                width: 196px;
                max-height: 204px;
            }
            .user {
                width: 278px;
                height: 278px;
                margin-right: 30px;
                margin-left: 30px;
            }
            .name {
                max-width: 256px;
                font-size: 32px;
                color: gold;
                text-transform: uppercase;
                font-family: Verdana;
                text-align: center;
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
                justify-content: center;
            }
            .inner {
                margin-bottom: 60px;
            }
        </style>
    </head>
    <body>
        <?php 
        foreach ( $ranks as $rank => $other ) {
            $genders = ['M','F'];
            foreach ( $genders as $gender ) {
                echo "<div class='rank'>" . $rank . "</div>";
                echo "<div style='page-break-after: always;'></div>";
                echo "<div class='row'>";
                $i = 0;
                $j = 0;
                foreach ( $other[$gender] as $user ) {
                    $img = '';
                    $pic = getPic( $user );
                    if ( !empty( $pic ) ) {
                        if ( $pic['mobile_pic'] ) {
                            $img = "mobile/reg/" . $pic['mobile_pic'];
                        } else if ( $pic['thumb'] ) {
                            $img = "thumbs/" . $pic['thumb'];
                        } else if ( $pic['user_photo_id'] ) {
                            $img = "file_view.php?id=" . $pic['user_photo_id'];
                        }
                    } 
                    if ( empty( $img ) ) {
                        if ( $gender == 'M' ) $img = "images/avatar_boy.jpg";
                        else if ( $gender == 'F' ) $img = "images/avatar_girl.png";
                    }

                    echo "<div class='col'>";
                    echo "<div class='row inner'>";
                    $school = $userSchool[$user];
                    if ( $logos[$school]['logo_boys'] || $logos[$school]['logo_girls'] ) {
                        if ( $gender == 'M' ) echo "<img class='school' src='schoolLogos/" . $logos[$school]['logo_boys'] . "' />"; 
                        else if ( $gender == 'F' ) echo "<img class='school' src='schoolLogos/" . $logos[$school]['logo_girls'] . "' />";
                    }
                    else echo "<img class='school' src='file_view.php?id=" . $logos[$school]['logo_id'] . "' />"; 
                    echo "</div>";

                    echo "<div class='row inner'><img class='user' src='" . $img . "' /></div>";
                    echo "<div class='row inner'><div class='name'>" . $userInfo[$user] . "</div></div>";
                    echo "</div>";

                    // start new row for every 6 kids
                    if ( ++$i == 11 ) {
                        echo "</div>";
                        if ( ++$j == 2 ) {
                            echo "<div style='page-break-after: always;'></div>";
                        }
                        echo "<div style='height: 50px;'></div><div class='row'>";
                        $i = 0;
                    }
                }
                echo "</div><div style='page-break-after: always'></div>";
            }
        }
        ?>
    </body>
</html>