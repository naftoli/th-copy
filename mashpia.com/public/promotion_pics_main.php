<?php
// school pic takes up 2 rows and 2 columns
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
    $rows = 2;
}

$numUsers = count( $users );
$numPicsInColumn = ceil( $numUsers / 2 );
if ( ($numPicsInColumn + $rows) > ROWS ) {
    $numPicsInColumn = ROWS - $rows;
}

if ( $logos[$school]['logo_boys'] || $logos[$school]['logo_girls'] ) {
    if ( $gender == 'M' ) echo "<img class='school' src='schoolLogos/" . $logos[$school]['logo_boys'] . "' />"; 
    else if ( $gender == 'F' ) echo "<img class='school' src='schoolLogos/" . $logos[$school]['logo_girls'] . "' />";
}
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