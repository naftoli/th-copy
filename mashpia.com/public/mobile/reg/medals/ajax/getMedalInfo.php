<?php
$admin_auth = ['school', 'user'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$user = mysql_real_escape_string( $_POST['user_id'] );

$monthly_subjects = [1, 12, 15, 93, 106];
//$ds_weekly = [126];
$ds_daily = [121, 122, 124, 125, 129, 130, 131, 132, 133, 134, 135];

function calculateNextDate($subject, $needed) {
    global $user, $monthly_subjects, $MASHPIA_DB;
    // find date of task for this subject in $needed times
    $jd = unixtojd();
    $limit_by = $needed;
    if (! in_array($subject, $monthly_subjects)) {
        $jd = $jd + ($needed * 7);
        $limit_by = 1;
    }
    $sql = "
        SELECT 
            end_date
        FROM
            date_tasks_missions dtm
                JOIN
            user_tracks ut USING (subject_id)
                JOIN
            users u USING (user_id)
        WHERE
            dtm.subject_id = $subject
                AND dtm.end_date >= $jd
                AND u.school_type_id = dtm.school_type_id
                AND ut.track_id = dtm.track_id
                AND ut.level = dtm.level
                AND u.user_id = $user
        GROUP BY dtm.end_date 
        LIMIT $limit_by";
//    echo $sql . "<br />";
    $stmt = $MASHPIA_DB->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) < $limit_by) return '';
    // get last row
    $date = $rows[$limit_by - 1]['end_date'];
    $jewish = jdtojewish($date, true, CAL_JEWISH_ADD_GERESHAYIM);
    $str = iconv('WINDOWS-1255', 'UTF-8', $jewish);
    return $str;
}

function getImgBack($subject_id, $medal) {
    $medal_name = strtolower($medal);
    if ($medal_name == 'gray') $medal_name = 'grey';
    $img = "http://mashpia.com/mobile/reg/";
    if ( in_array( $subject_id, [ 1, 12, 15, 93 ] ) )
        $img .= 'images/backs/wwtc/'.$medal_name.'.png';
    // 585 total missions
    else if ( in_array( $subject_id, [ 40, 94 ] ) )
        $img .= 'images/backs/yd/'.$medal_name.'.png';
    // day schools subjects
    else if ( $subject_id > 120 && $subject_id <= 135 ) {
        if ( !in_array( $subject_id, [ 126, 127, 135 ] ) ) {
            // daily subjects / tasks
            $img .= 'images/backs/daySchools/daily/' . $medal_name . '.png';
        } else if ($subject_id == 127) {
            // yd subjects / tasks
            $img .= 'images/backs/daySchools/yd/' . $medal_name . '.png';
        } else {
            // weekly subjects / tasks
            $img .= 'images/backs/daySchools/weekly/' . $medal_name . '.png';
        }
    }
    // 375 missions
    else
        $img .= 'images/backs/weekly/'.$medal_name.'.png';

    return $img;
}

// figure out which subjects we are showing
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.campaignEnrollment.php';
$c = new CampaignEnrollment($user);
$c->setType();
$subjects = $c->getCampaigns();

$subjectIcons = [];
$subjectNames = array();
$sql = "SELECT subject_id, subject_name, subject_gold_image_id FROM subjects WHERE subject_id IN (" . implode( ',', $subjects ) . ")";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	$name = $row['subject_name'];
	// correct the mission names
	if ($row['subject_id'] == 1) {
		$name = "תהילים";
	} else if ($row['subject_id'] == 27) {
		$name = "תניא";
	}
	$subjectNames[$row['subject_id']] = $name;
	$subjectIcons[$row['subject_id']] = $row['subject_gold_image_id'];
}
// get the missions
$missions = array();
$sql = "SELECT subject_id, SUM( mission_count ) AS total
		FROM date_tasks_mission_marks 
		WHERE user_id = $user 
		GROUP BY subject_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $subject = $row['subject_id'];
    $total = $row['total'];
    $sql2 = "SELECT medal_ord, medal_name, medal_name_he, missions_required, profile_photo_id FROM medals_subjects 
             JOIN medals USING (medal_ord)    
             WHERE subject_id = " . $subject . " 
             ORDER BY medal_ord"; 
    //echo $sql2 . "<br />"; 
    $result2 = mysql_query( $sql2 );
    $needed = 0;
    $base_amount = 0;
	$tempMedals = array();
	$tempMedalPics = array();
	$ctr = 0;
    while ( $row2 = mysql_fetch_assoc( $result2 ) ) {
        $tempMedals[$ctr] = ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ? $row2['medal_name_he'] : $row2['medal_name'] );
        if ( $row2['profile_photo_id'] ) $tempMedalPics[$ctr] = $row2['profile_photo_id'];
        $required = (int)$row2['missions_required'];
        $base_amount = $needed;
        $needed += $required;

        if ( $needed > $total ) {
        	if ($ctr == 0) {
        		$missions[$subject] = array(
        		    'ord'   =>  $row2['medal_ord'],
	            	'medal'	=>	"None", 
	            	'photo'	=>	"", 
                    'left'	=>	$needed - $total,
                    'total' =>  $total,
                    'needed' => $needed,
                    'base_amount' => $base_amount
				);
        	} else {
	            $missions[$subject] = array(
	                'ord'   =>  $row2['medal_ord'],
	            	'medal'	=>	$tempMedals[$ctr-1], 
	            	'photo'	=>	$tempMedalPics[$ctr-1], 
                    'left'	=>	$needed - $total,
                    'total' =>  $total,
                    'needed' => $needed,
                    'base_amount' => $base_amount
				);
			}
            break;
        }
		$ctr++;
    }
    if ( $total >= $needed ) {
        $missions[$subject] = array(
            'medal'	=>	$tempMedals[$ctr-1], 
            'photo'	=>	end($tempMedalPics), 
            'left'	=>	0,
            'total' =>  $total,
            'needed' => $needed,
            'base_amount' => $base_amount
        );
    }
}

$medals = array();
$sql = "select medal_ord, medal_name, medal_name_he from medals";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$medals[$row['medal_ord']] = (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ? $row['medal_name_he'] : $row['medal_name']);
}

// keep info on all medals within all subjects
$subject_medals = [];
foreach ($subjects as $subject) {
    $sql = "SELECT medal_ord, missions_required, profile_photo_id FROM medals_subjects 
             WHERE subject_id = " . $subject . " 
             
             ORDER BY medal_ord";
    $result = mysql_query($sql);
    $needed = 0;
    while ($row = mysql_fetch_assoc($result)) {
        $needed += $row['missions_required'];
        $subject_medals[$subject][$row['medal_ord']] = [
            'img' => $row['profile_photo_id'],
            'missions_needed' => $needed
        ];
    }
}

$imgColors = ['#d8dbde', '#aa2834', '#d55027', '#d29020', '#23914b', '#2b3078', '#4d297b', '#6d4c38', '#71707b', '#0a0515',
    '#8e909d', '#ad6b44', '#b78f4b'];

foreach ( $subjects as $subject ) {
    $monthly = in_array($subject, $monthly_subjects);
    if ( array_key_exists($subject, $missions) ) {
    	$mission = $missions[$subject];
    	$medal = $mission['medal'];
    	$photo = $mission['photo'];
		$left = $mission['left'];
		$key = array_search($medal, $medals);

		// create array of medals
        $i = 0;
        $medals_arr = [];
        foreach ($subject_medals[$subject] as $ord => $details) {
            $medals_arr[] = [
                'number'    => $details['missions_needed'],
                'img'       => getImgBack($subject, $medals[$ord]),
                'completed' => intval($mission['total']) >= $details['missions_needed'],
                'color'     => $imgColors[$i++]
            ];
//            if ($details['missions_needed'] > intval( $mission['total'] )) break;
        }

		$info[] = array( 
			'id'	=>	$subject, 
			'name'	=>	$subjectNames[$subject],
			'icon'  =>  $subjectIcons[$subject],
			'medal'	=>	$medal, 
			'photo'	=>	$photo,
            'left'	=>	$left,
            'total'	=>	intval( $mission['total'] ), 
            'needed'=>	$mission['needed'],
            'base_amount'=>	$mission['base_amount'], 
			'next'	=>	($key === false ? 1 : ++$key),
            'nextMedalDate' => calculateNextDate($subject, $mission['left']),
            'nextMedalImg'  => $subject_medals[$subject][$mission['ord']]['img'],
            'nextMedalColor'    => $imgColors[$mission['ord']],
            'monthly'   => $monthly,
            'weekly'    => !$monthly,
            'medals'    => $medals_arr,
            'daily'     => in_array($subject, $ds_daily)
		);
    } else {
        // create array of medals
        $i = 0;
        $medals_arr = [];
        foreach ($subject_medals[$subject] as $ord => $details) {
            $medals_arr[] = [
                'number'    => $details['missions_needed'],
                'img'       => getImgBack($subject, $medals[$ord]),
                'completed' => false,
                'color'     => $imgColors[$i++]
            ];
//            if ($details['missions_needed'] > intval( $mission['total'] )) break;
        }

    	$info[] = array( 
			'id'	=>	$subject, 
			'name'	=>	$subjectNames[$subject],
            'icon'  =>  $subjectIcons[$subject],
			'medal'	=>	"None", 
			'photo'	=>	"", 
            'left'	=>	(int)$subject_medals[$subject][1]['missions_needed'],
            'total'	=>	0, 
            'needed'=>	(int)$subject_medals[$subject][1]['missions_needed'],
			'next'	=>	1,
            'nextMedalDate' => $left ? calculateNextDate($subject, $left) : '',
            'nextMedalImg'  => $subject_medals[$subject][1]['img'],
            'nextMedalColor'    => $imgColors[1],
            'monthly'   => $monthly,
            'weekly'    => !$monthly,
            'medals'    => $medals_arr,
            'daily'     => in_array($subject, $ds_daily)
		);
    }
}

echo json_encode($info);
?>