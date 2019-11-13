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
            .rank {
                top: 77px;
                width: 3657px;
                border: 1px solid black;
                height: 250px;
                margin-bottom: 10px;
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
                $newBox = "</div></div></div></div><div class='rank'>" . $rank . "</div><div class='row'><div class='col'>";

                echo "<div class='rank'>" . $rank . "</div>";
                echo "<div class='row'>";
                echo "<div class='col'>";
                foreach ( $info as $school => $users ) {                    
                    $rows += 2;
                    // check if adding school pic will go past max rows
                    if ( $rows > ROWS ) {
                        // we need to create new column from top of page
                        echo $newColumn;
                        $rows = 2;
                        $cols++;
                        // if column number is even add one (was subtracted at end of script)
                        if ( $cols % 2 == 0 ) $cols++;
                    }

                    if ( $cols >= COLS ) {
                        // end current box and start new one
                        echo $newBox;
                        $cols = 1;
                    }

                    // check if school will be at bottom left of page
                    if ( $cols >= 19 && $rows >= 9 ) {
                        echo $newBox;
                        $cols = 1;
                        $rows = 0;
                    }

                    $numUsers = count( $users );
                    $numPicsInColumn = ceil( $numUsers / 2 );
                    if ( ($numPicsInColumn + $rows) > ROWS ) {
                        $numPicsInColumn = ROWS - $rows;
                    } 

                    if ( $logos[$school]['logo'] ) echo "<img class='school' src='schoolLogos/" . $logos[$school]['logo'] . "' />"; 
                    else echo "<img class='school' src='file_view.php?id=" . $logos[$school]['logo_id'] . "' />"; 
                    // if column number is even add one (was subtracted at end of script)
                    if ( $cols % 2 == 0 ) $cols++;

                    if ( $rows == ROWS ) {
                        // we need to create new column from top of page for pics   
                        echo $newColumn;       
                        $rows = 0;
                        $cols++;
                        // if column number is even add one (was subtracted at end of script)
                        if ( $cols % 2 == 0 ) $cols++;

                        // if ( $cols >= COLS ) {
                        //     // if columns is past max then create new box
                        //     echo $newBox;
                        //     $cols = 1;
                        // } else {
                        //     echo $newColumn;
                        // }
                        
                        $numPicsInColumn = ceil( $numUsers / 2 );
                        if ( ($numPicsInColumn + $rows) > ROWS ) {
                            $numPicsInColumn = ROWS - $rows;
                        }
                    }
                    
                    $num = 0;
                    echo "<div class='row'>";
                    echo "<div class='col'>";
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
                        $rows++;
                        //echo "Row: " . $rows . " Col: " . $cols . "<br />";
                        //echo "Num Users: " . $numUsers . " Half Point: " . $numPicsInColumn;

                        // check if we need to create new column for second half of pics
                        if ( ++$num >= $numPicsInColumn && $idx < ($numUsers - 1) ) {
                            //echo "Num Users: " . $numUsers . " Num: " . $num . " Num Pics in Col: " . $numPicsInColumn . " Index: " . $idx;
                            if ( $cols % 2 == 1 ) {
                                // we need to go into second column under school pic
                                $rows = $firstPic;
                                echo $newColumn;
                            } else {
                                // we need to create new column from top of page
                                $rows = 0;
                                $firstPic = 0;
                                if ( $cols >= COLS ) {
                                    // end current box and start new one
                                    echo $newBox;
                                    echo "<div class='row'><div class='col'>";
                                    $cols = 0;
                                } else {
                                    //echo "STARTING NEW ROW";
                                    echo $newRow;
                                }

                                // recalulate how many pics per column based on remaining pics
                                $numLeft = $numUsers - $idx - 1;
                                $numPicsInColumn = ceil( $numLeft / 2 );
                                if ( ($numPicsInColumn + $rows) > ROWS ) {
                                    $numPicsInColumn = ROWS - $rows;
                                } 
                            }
                            $num = 0;
                            $cols++;
                        } 
                    }
                    // add to rows if there were odd number of pics and ther were two columns of pics
                    if ( $numUsers % 2 == 1 && $cols % 2 == 0 ) $rows++;
                    echo "</div></div>";
                    if ( $rows >= ROWS ) {
                        echo $newColumn;
                        $rows = 0;
                        $cols++;
                    } else {
                        if ( $cols % 2 == 0 ) $cols--; // we are moving back to first column of pics after school pic 
                    }
                }
                echo "</div></div>";
            }
        }
        ?>
    </body>
</html>