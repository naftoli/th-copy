<?php
$admin_auth = ['school'];
require_once 'header.php';
require_once 'class.rankReport.php';
$r = new RankReport;
$r->setRanks('byRankFirst', 3);
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
define('ROWS', 10);
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
                font-size: 72px;
            }
            .school {
                width: 360px;
                height: 360px;
            }
            .user {
                width: 360px;
                height: 360px;
            }
            img {
                margin-right: 6px;
                margin-bottom: 6px;
            }
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
            .grid {
                border: 1px solid #fff;
                margin: 150px;
                padding: 50px;
                width: 3657px;
                height: 1830px;
            }
        </style>
    </head>
    <body>
        <?php 
        $genders = ['M','F'];
        foreach ( $genders as $gender ) {
            foreach ( $ranks[$gender] as $rank => $info ) {
                // vars to keep track of how many cols and rows there are
                $cols = 2;
                $rows = 0;

                $newColumn = "</div><div class='col'>";
                $newBox = "</div></div></div><div style='page-break-after: always'></div><div class='grid'><div class='row'><div class='col'>";

                echo "<div class='grid'>";
                echo "<div class='row'>";
                echo "<div class='col'>";
                foreach ( $info as $school => $users ) {                    
                    // check if adding school pic will go past max rows
                    if ( $rows >= ( ROWS - 2 ) ) {
                        // we need to create new column from top of page
                        echo $newColumn;
                        $rows = 0;
                        $cols += 2;
                    }
                    
                    if ( $cols > COLS ) {
                        // end current box and start new one
                        echo $newBox;
                        $cols = 2;
                    }
                    
                    if ( $logos[$school]['logo_boys'] || $logos[$school]['logo_girls'] ) {
                        if ( $gender == 'M' ) echo "<img class='school' src='schoolLogos/" . $logos[$school]['logo_boys'] . "' />"; 
                        else if ( $gender == 'F' ) echo "<img class='school' src='schoolLogos/" . $logos[$school]['logo_girls'] . "' />";
                    }
                    else echo "<img class='school' src='file_view.php?id=" . $logos[$school]['logo_id'] . "' />"; 
                    $rows += 2;
                    
                    foreach ( $users as $idx => $user_id ) {
                        // keep track of what row number the first pic for this school is
                        if ( $idx == 0 ) {
                            $firstPic = $rows;
                        }
                        $img = '';
                        $pic = getPic( $user_id );
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
                        echo "<img class='user' src='" . $img . "' />";
                        $rows += 2;
                        // echo "Row: " . $rows . " Col: " . $cols . "<br />";
                        // echo "Num Users: " . $numUsers . " Half Point: " . $numPicsInColumn;

                        // check if adding school pic will go past max rows
                        if ( $rows >= ROWS ) {
                            // we need to create new column from top of page
                            echo $newColumn;
                            $rows = 0;
                            $cols += 2;
                        }
                        
                        if ( $cols > COLS ) {
                            // end current box and start new one
                            echo $newBox;
                            $cols = 2;
                        }                    
                    }
                }
                echo "</div></div></div>";
            }
        }
        ?>
    </body>
</html>