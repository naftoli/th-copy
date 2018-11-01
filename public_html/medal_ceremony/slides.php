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
	$sql = "select rank_name 
			from ranks r 
			join rank_marks rm 
			using (rank_ord) 
			join users u 
			using (user_id) 
			where u.user_id = " . $user . " 
			order by rm.rank_ord desc 
			limit 0,1";
	$result = mysql_query( $sql ) or die( mysql_error() );
	$row = mysql_fetch_assoc( $result );
	return $row['rank_name'];
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
        <style>
            .slide {
                background-image: url("Medal_Ceremony.jpg");
                background-repeat: no-repeat;
                width: 1200px;
                height: 750px;
            }
            .heDate {
                direction: rtl;
                width: 1200px;
                margin: auto;
                text-align: center;
                padding-top: 100px;
                font-size: 30px;
            }
            .medal {
                float: left;
                position: absolute;
            }
            .row1 {
                margin-top: -25px;
            }
            .row2 {
                margin-top: 115px;
            }
            .row3 {
                margin-top: 252px;
            }
            .row4 {
                margin-top: 390px;
            }
            .column1 {
                margin-left: 255px;
            }
            .column2 {
                margin-left: 515px;
            }
            .column3 {
                margin-left: 780px;
            }
            .column4 {
                margin-left: 1060px;
            }
        </style>
    </head>

    <body>
        <?php
        foreach ( $schools as $school_id => $school_name ) {
            $m->setSchoolId( $school_id );
            $m->setMedalDetails();
            $details = $m->getMedalDetails();
            $userInfo = $m->getUserInfo();
            $subjects = $m->getSubjects();

            if (count($details)) {
                foreach ( $details as $school => $line ) {
                    if ( $school != $school_name ) continue;
                    foreach ( $line as $teacher => $class ) {
                        foreach ( $class as $grade => $info ) {
                            foreach ( $info as $user => $medals ) {
                                echo "<div class='slide'>";
                                echo "<div class='heDate'>" . $heDatesMedals['start_he'] . ' - ' . $heDatesMedals['end_he'] . "</div>";
                                echo "<div class='rank'>" . getRank( $user ) . "</div>";
                                echo "<div class='user'>" . $userInfo[$user] . "</div>";
                                echo "<div class='info'>Grade " . $grade . " * " . $teacher . " * " . $school . "</div>";
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