<?php
$admin_auth = array('school'); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/medal_board/class.medalBoard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

// if ( !isset( $_POST['date'] ) ) {
//     header("Location: options.php");
//     exit;
// }

$m = new MedalBoard(); 
$m->setNameLang('en');

// figure out which dates to show
// $m->setDateSelection();
// $end = $_POST['date'];
// $key = array_search( $end, $m->dates );
// $start = $m->dates[$key - 1];
// $m->overrideDates( $start, $end );

$dates = $m->getReportDates();
$heDatesMedals = $m->getHeReportDates();

function getLogo( $school_id ) {
    $sql = "select logo from schools where school_id = " . $school_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    return $row['logo'];
}

function getRank($user) {
    $sql = "select rank_ord 
            from rank_marks rm 
			join users u 
			using (user_id) 
			where u.user_id = " . $user . " 
			order by rm.rank_ord desc 
			limit 1";
    $result = mysql_query( $sql );
	$row = mysql_fetch_assoc( $result );
	return $row['rank_ord'];
}

function getMedal( $subject_id, $medal ) {
    $medal_name = strtolower( $medal );
    $medal_name = $medal_name == 'gray' ? 'grey' : $medal_name;
    // 95 total missions
    if ( in_array( $subject_id, [ 1, 12, 15, 93 ] ) )
        return 'images/backs/wwtc/'.$medal_name.'.png';
    // 585 total missions
    else if ( in_array( $subject_id, [ 40, 94 ] ) )
        return 'images/backs/yd/'.$medal_name.'.png';
    // 375 missions
    else
        return 'images/backs/weekly/'.$medal_name.'.png';
}

function getUserPhoto( $user_id ) {
    $sql = "select u.mobile_pic, u.user_photo_id, t.thumb 
            from users u 
            left join thumbs t on u.user_photo_id = t.file_id 
            where u.user_id = " . $user_id;
    $result = mysql_query( $sql );
    if ( mysql_num_rows( $result ) > 0 ) {
        $row = mysql_fetch_assoc( $result );
        if ( $row['thumb'] && file_exists('https://mashpia.com/mobile/reg/thumbs/' . $row['thumb']) ) return 'https://mashpia.com/mobile/reg/thumbs/' . $row['thumb'];
        else if ( $row['mobile_pic'] ) return 'https://mashpia.com/mobile/reg/' . $row['mobile_pic'];
        else if ( $row['user_photo_id'] ) return 'https://mashpia.com/file_view.php?id=' . $row['user_photo_id'];
    }
    return '';
}

function getTotalMedalsEarned( $user_id ) {
    $sql = "select count(*) as total from medal_marks where user_id = " . $user_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    return isset( $row['total'] ) ? $row['total'] : 0;
}

function getPositioning( $user_id ) {
    // determine which subjects go in which column for css styling / positioning
    $positioning = [ 4, 45, 21, 100, 90, 42, 27, 13, 16, 41, 12, 1, 40 ];
    $positioningFrum = [ 4, 45, 21, 100, 90, 42, 27, 92, 16, 41, 93, 1, 94 ];

    $sql = "select school_type_id from users where user_id = " . $user_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $type_id = $row['school_type_id'];
    switch ($type_id) {
        case 2:
        case 3:
            return $positioning;
        case 12:
        case 13:
            return $positioningFrum;
        default:
            return $positioning;
    }
}

$heDateArr = explode('/', jdtojewish( unixtojd() ) );
$heMonths = ['','Tishrei','Cheshvon','Kislev','Teves','Shvat','Adar','Adar II','Nissan','Iyar','Sivan','Tamuz','Av','Elul'];
$heDate = $heDateArr[1] . ' ' . $heMonths[ $heDateArr[0] ] . ' ' . $heDateArr[2];

$ranks = [];
$sql = "select rank_ord, rank_name, rank_image_id from ranks";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $ranks[$row['rank_ord']] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf8' />
        <link rel="stylesheet" href="medal_board.css" />
    </head>

    <body>
        <div class="instructions">
            To save the slideshow as a pdf, click print (or control P), then save as pdf
        </div>
        <?php
        $schoolInfo = explode('|', $_POST['school']);
        $school_id = $schoolInfo[0];
        $school_name = $schoolInfo[1]; 
        $m->setSchoolId( $school_id );
        if ( $_POST['grades'] > 0 ) $m->setGrades( $_POST['grades'] );
        $m->setMedalDetails();
        $m->setMissionsInfo();

        $details = $m->getMedalDetails();
        $userInfo = $m->getUserInfo();
        $subjects = $m->getSubjects();
        $missionsInfo = $m->getMissionsInfo();

//        echo "<pre>"; print_r($details); echo "</pre>";

        $cssProps = ['','White','Red','Orange','Yellow','Green','Blue','Purple','Brown','Gray','Black']; // index for css color properties of mission totals

        if ( !empty($details) ) {
            foreach ( $details as $school => $line ) {
                foreach ( $line as $grade => $class ) {
                    foreach ( $class as $teacher => $info ) {
                        foreach ( $info as $user => $medals ) {
                            $rank = getRank( $user );
                            $p = new Points( $user );
                            echo "<div class='slide'>";
                            echo "<div class='logo'><img src='https://mashpia.com/schoolLogos/" . getLogo( $school_id ) . "' /></div>";
                            echo "<div class='photo'><img src='" . getUserPhoto( $user ) . "' /></div>";
                            echo "<div class='userInfo'><div class='user'>" . $ranks[$rank]['rank_name'] . ' ' . $userInfo[$user] . "</div>";
                            echo "<div class='teacher'>Grade " . $grade . " &#9679; " . $teacher . "</div></div>";
                            echo "<div class='date'>" . $heDate . "</div>";
                            echo "<div class='numMedals'>" . getTotalMedalsEarned( $user ) . "</div>";
                            echo "<div class='numMiles'>" . $p->getTotalPoints() . " Miles</div>";

                            // echo "<div class='heDate'>" . $heDatesMedals['start_he'] . ' - ' . $heDatesMedals['end_he'] . "</div>";
                            // echo "<div class='userInfo'><div class='rank'><img src='https://mashpia.com/file_view.php?id=" . $rankInfo['rank_image_id'] . "' />" . 
                            //     $rankInfo['rank_name'] . "</div>";
                            for ( $i = 1; $i <= $rank; $i++ ) {
                                $rank_pos = 'rank' . $i;
                                echo "<div class='rank $rank_pos'><img src='https://mashpia.com/file_view.php?id=" . $ranks[$i]['rank_image_id'] . "' /></div>";
                            }

                            // find out which subjects to show
                            $order = getPositioning( $user );
//                            echo "<pre>"; print_r($order); print_r($subjects); echo "</pre>";
                            foreach ( $order as $column => $subject ) {
                                $col_pos = "column" . ++$column;

                                // find name of subject to use for key in medals array
                                $subject_name = array_search($subject, $subjects);
//                                echo "Subject: " . $subject . " Subject Name: " . $subject_name . "<br />";

                                $row = 0; // start at row 1, each medal goes 46 pixels higher
                                foreach ( $medals[$subject_name] as $medal ) {
                                    $row_pos = "row" . ++$row;
                                    $image = "/mobile/reg/" . getMedal( $subject, $medal );
                                    echo "<div class='medal " . $col_pos . ' ' . $row_pos . "'><img src='" . $image . "' ";
                                    echo "/></div>";
                                }
                                $css = $cssProps[$row];
                                echo "<div class='medal " . $col_pos . ' ' . $css . " missionsDone'>" . $missionsInfo[$user][$subject]['done'];
                                echo "/" . $missionsInfo[$user][$subject]['max'] . "</div>";
                                echo "<div class='medal " . $col_pos . ' ' . " missionsLeft'>" . $missionsInfo[$user][$subject]['left'];
                                if ( $missionsInfo[$user][$subject]['left'] != 'Completed!' ) echo ' to ' . $missionsInfo[$user][$subject]['color'];
                                echo "</div>";
                            }
//                            exit;

                            echo "</div>";
                            echo "<div style='page-break-after: always; clear: both'></div>";
                        }
                    }
                }
            }
        }
        ?>
    </body>
</html>