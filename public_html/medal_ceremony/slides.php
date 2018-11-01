<?php
$admin_auth = array('school'); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.medalReport.php';
$m = new MedalReport();
$dates = $m->getReportDates();
$heDatesMedals = $m->getHeReportDates();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

function getRank($user) {
	$sql = "select rank_name, rank_image_id  
			from ranks r 
			join rank_marks rm 
			using (rank_ord) 
			join users u 
			using (user_id) 
			where u.user_id = " . $user . " 
			order by rm.rank_ord desc 
			limit 0,1";
    $result = mysql_query( $sql );
	$row = mysql_fetch_assoc( $result );
	return $row;
}

function getMedal( $subject_id, $medal ) {
    $medal_name = strtolower( $medal );
    $medal_name = $medal_name == 'gray' ? 'grey' : $medal_name;
    // 95 total missions
    if ( in_array( $subject_id, [ 1, 12, 15, 93 ] ) )
        return 'images/backs/wwtc/'.$medal_name.'.gif';
    // 585 total missions
    else if ( in_array( $subject_id, [ 40, 94 ] ) )
        return 'images/backs/yd/'.$medal_name.'.gif';
    // 375 missions
    else
        return 'images/backs/weekly/'.$medal_name.'.gif';
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

// determine which subjects go in which row / column for css styling / positioning
$positioning = [
    [0,0,0,41],
    [40,100,90,42],
    [1,4,27,13],
    [12,45,21,16]
];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf8' />
        <link rel="stylesheet" href="slide.css" />
    </head>

    <body>
        <?php
        foreach ( $schools as $school_id => $school_name ) {
            $m->setSchoolId( $school_id );
            $m->setMedalDetails();
            $details = $m->getMedalDetails();
            $userInfo = $m->getUserInfo();
            $subjects = $m->getSubjects();

            if ( count($details) ) {
                foreach ( $details as $school => $line ) {
                    if ( $school != $school_name ) continue;
                    foreach ( $line as $teacher => $class ) {
                        foreach ( $class as $grade => $info ) {
                            foreach ( $info as $user => $medals ) {
                                $rankInfo = getRank( $user );
                                echo "<div class='slide'>";
                                echo "<div class='heDate'>" . $heDatesMedals['start_he'] . ' - ' . $heDatesMedals['end_he'] . "</div>";
                                echo "<div class='photo'><img src='" . getUserPhoto( $user ) . "' /></div>";
                                echo "<div class='userInfo'><div class='rank'><img src='https://mashpia.com/file_view.php?id=" . $rankInfo['rank_image_id'] . "' />" . 
                                    $rankInfo['rank_name'] . "</div>";
                                echo "<div class='user'>" . $userInfo[$user] . "</div>";
                                echo "<div class='info'>Grade " . $grade . " &#9679; " . $teacher . " &#9679; " . $school . "</div></div>";
                                foreach ( $medals as $subject => $more ) {
                                    // figure out which row and column the subject belongs in 
                                    for ($row = 0; $row < 4; $row++) {
                                        for ($column = 0; $column < 4; $column++) {
                                            if ( $subjects[$subject] == $positioning[$row][$column] ) {
                                                break 2;
                                            }
                                        }
                                    }
                                    $row_pos = "row" . ++$row;
                                    $col_pos = "column" . ++$column;

                                    // get highest medal
                                    $max = count( $more );
                                    $medal = $more[$max-1];
                                    $image = "/mobile/reg/" . getMedal( $subjects[$subject], $medal );
                                    echo "<div class='medal " . $row_pos . ' ' . $col_pos . "'><img src='" . $image . "' /></div>";
                                }
                                echo "</div>";
                                echo "<div style='page-break-after: always'></div>";
                            }
                        }
                    }
                }
            }
        }
        ?>
    </body>
</html>