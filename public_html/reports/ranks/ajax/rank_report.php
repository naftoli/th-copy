<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ( $admin_user['auth'] !== "super" ) {
    echo "Single School Report Coming Soon!"; die();
}

function dateToJd( $date ){
    $date = explode( "-", $date );
    return gregoriantojd($date[1], $date[2], $date[0]);
}

$from = dateToJd( $_POST['from'] );
$to = dateToJd( $_POST['to'] );

$rank_promotions_query = mysql_query(
     " SELECT s.school_name, s.hachayol_name, u.first, u.last, u.user_serial, u.mobile_pic, u.user_photo_id, "
    ." r.rank_name, rm.rank_ord FROM rank_marks rm "
    ." JOIN users u USING ( user_id ) "
    ." JOIN ranks r USING ( rank_ord ) "
    ." JOIN schools s USING ( school_id ) "
    ." WHERE rm.date_promoted > '$from' "
    ." AND rm.date_promoted < '$to' "
    ." AND rm.rank_ord > 1 "
    ." AND s.test_school = 0 "
    ." AND s.chayolei = 1 "
    ." ORDER BY r.rank_ord DESC, s.school_name, u.last, u.first "
);

$totals_query = mysql_query(
    " SELECT r.rank_name, COUNT(*) as total FROM rank_marks rm "
    ." JOIN users u USING ( user_id ) JOIN ranks r USING ( rank_ord ) "
    ." JOIN schools s USING ( school_id ) WHERE rm.date_promoted > '$from' "
    ." AND rm.date_promoted < '$to' AND rm.rank_ord > 1 "
    ." AND s.test_school = 0 AND s.chayolei = 1 "
    ." GROUP BY rm.rank_ord ORDER BY r.rank_ord DESC"
);
?>
<h2>Totals</h2>
<a data-clipboard-target="#totals" class="btn button">Copy to clipboard</a>
<div id="totals">
    <?php
        while( $total = mysql_fetch_assoc( $totals_query ) ){
            echo $total['rank_name'] . " - " . $total['total'] . "<br/>";
        }
    ?>
</div>
<h2>Breakdown</h2>
<a data-clipboard-target="#breakdown" class="btn button">Copy to clipboard</a>
<a id="zip-images" class="btn button">Generating Download....</a>
<div id="breakdown">
    <?php
    $prev_rank = "";
    $prev_school = "";
    $cutoff = 9;

    while ( $promotion = mysql_fetch_assoc( $rank_promotions_query) ){
        $school_name = $row['hachayol_name'] ? $promotion['hachayol_name']  : $promotion['school_name'];
        if ( $promotion['rank_name'] != $prev_rank )
            echo "<br/><span class='rank'>" . $promotion['rank_name'] . "</span><br />";
        
        if ( $promotion['rank_ord'] < $cutoff && $promotion['school_name'] != $prev_school ) {
            echo "<span class='school top'>$school_name</span><br />";
        }

        $prev_rank = $promotion['rank_name'];
        $prev_school = $promotion['school_name'];
        
        $first = ucwords(strtolower($promotion['first']));
        if ( $promotion['rank_ord'] < $cutoff && strpos( $first, ' ' )) {
            $first_names = explode( ' ', $first );
            foreach( $first_names as $index => $name ) {
                $first_names[$index] = mb_substr( $name, 0, 1 );
            }
            $first = implode( ' ', $first_names );
        } else if ( $promotion['rank_ord'] < $cutoff ) {
            $first = mb_substr( $first, 0, 1 );
        }
        $name = $first . " " . ucwords(strtolower($promotion['last']));

        // show images for generals
        if ($promotion['rank_ord'] == $cutoff ) {
            echo "<a class='name profile' data-profile='" . 
                ( $promotion['mobile_pic'] ? 
                    "//mashpia.com/mobile/reg/" . $promotion['mobile_pic'] :
                    ( $promotion['user_photo_id'] ? "/file_view.php?id=" . $promotion['user_photo_id'] : "/mobile/reg/images/profile-photo-default.jpg" )
                ) . "' href='/reports/users/student_info.php?serial=" . $promotion['user_serial'] . "' "
                . " target='_blank' rel='noopener noreferrer'>$name</a><br />";
        } else {
            echo "<a class='name' href='/reports/users/student_info.php?serial=" . $promotion['user_serial'] . "' target='_blank' rel='noopener noreferrer'>$name</a><br />";
        }

        if ($promotion['rank_ord'] >= $cutoff ) {
            echo "<span class='school'>$school_name</span><div class='clearfix'></div>";
        }
    }
    ?>
</div>