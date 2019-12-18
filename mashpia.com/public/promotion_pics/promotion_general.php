<?php 
// vars to indicate how many rows and columns should show
define('ROWS', 11);
define('COLS', 20);

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

foreach ( $ranks as $rank => $other ) {
    $genders = ['M','F'];
    foreach ( $genders as $gender ) {
        echo "<div class='grid'>";
        echo "<div class='row'>";
        $i = 0;
        foreach ( $other[$gender] as $user ) {
            $img = '';
            $pic = getPic( $user );
            if ( !empty( $pic ) ) {
                if ( $pic['mobile_pic'] ) {
                    $img = "../mobile/reg/" . $pic['mobile_pic'];
                } else if ( $pic['thumb'] ) {
                    $img = "../thumbs/" . $pic['thumb'];
                } else if ( $pic['user_photo_id'] ) {
                    $img = "../file_view.php?id=" . $pic['user_photo_id'];
                }
            } 
            if ( empty( $img ) ) {
                if ( $gender == 'M' ) $img = "../images/avatar_boy.jpg";
                else if ( $gender == 'F' ) $img = "../images/avatar_girl.png";
            }

            echo "<div class='col'>";
            echo "<div class='row inner'>";
            $school = $userSchool[$user];
            if ( $logos[$school]['logo_boys'] || $logos[$school]['logo_girls'] ) {
                if ( $gender == 'M' ) echo "<img class='school' src='../schoolLogos/" . $logos[$school]['logo_boys'] . "' />"; 
                else if ( $gender == 'F' ) echo "<img class='school' src='../schoolLogos/" . $logos[$school]['logo_girls'] . "' />";
            }
            else echo "<img class='school' src='../file_view.php?id=" . $logos[$school]['logo_id'] . "' />"; 
            echo "</div>";

            echo "<div class='row inner'><img class='user' src='" . $img . "' /></div>";
            echo "<div class='row inner'><div class='name'>" . $userInfo[$user] . "</div></div>";
            echo "</div>";

            // start new row for every 6 kids
            if ( ++$i == 6 ) {
                echo "</div></div><div class='grid'><div class='row'>";
                $i = 0;
            }
        }
        echo "</div></div><div style='page-break-after: always'></div>";
    }
}
?>