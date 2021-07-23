<?
// load up the database
require_once( dirname(__FILE__) . '/../../../../db.php' );
$user = mysql_real_escape_string( $_POST['user_id'] );

$monthly_subjects = [1, 12, 15, 93, 106];

function calculateNextDate($subject, $needed) {
    // find date of task for this subject in $needed times
    $jd = unixtojd();
    $sql = "select end_date from date_tasks_missions where subject_id = $subject and end_date > $jd group by end_date limit $needed";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) == $needed) {
        // we have the correct result
        while ($row = mysql_fetch_assoc($result)) {}
        $date = $row['end_date'];
        $jewish = jdtojewish($date, true, CAL_JEWISH_ADD_GERESHAYIM);
        $str = iconv ('WINDOWS-1255', 'UTF-8', $jewish);
        return $str;
    } else {
        return '';
    }
}

// figure out which subjects we are showing
require '../../../../class.campaignEnrollment.php';
$c = new CampaignEnrollment($user);
$c->setType();
$subjects = $c->getCampaigns();

$subjectNames = array();
$sql = "SELECT subject_id, subject_name FROM subjects WHERE subject_id IN (" . implode( ',', $subjects ) . ")";
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
	//echo "Subject: " . $subject . ", Total: " . $total . "<br />";
	/*
	if ($subject == 27) {
		//if we are in tanya, get total from new system and add to old system
		//get tanya missions done
		//first get user barcode
		$sqlT = "select user_code from users where user_id = " . $user;
		$resT = mysql_query( $sqlT );
		$rowT = mysql_fetch_assoc( $resT );
		$barcode = '3' . $rowT['user_code'];
		
		$newTotal = header_v2_missions( array( 'arrUserCodes' => array( $barcode ) ) );
		if (isset($newTotal[$barcode]) && !empty($newTotal[$barcode])) 
		    $total += $newTotal[$barcode];		
	}
	*/
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
            'left'	=>	$needed - $total,
            'total' =>  $total,
            'needed' => $needed,
            'base_amount' => $base_amount
        );
    }
}
//echo "<pre>"; print_r( $missions ); echo "</pre>";

//array to hold required missions to reach first medal for each subject if no missions where done
//$medal_subjects = array();
//$sql = "SELECT subject_id, missions_required, profile_photo_id
//        FROM medals_subjects
//        WHERE medal_ord = 1
//        ORDER BY subject_id";
//$result = mysql_query($sql);
//while ($row = mysql_fetch_assoc($result)) {
//    $medal_subjects[$row['subject_id']] = array(
//    	'photo'	=>	$row['profile_photo_id'],
//		'left'	=>	$row['missions_required']
//	);
//}

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
             AND medal_ord <= 10 
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

foreach ( $subjects as $subject ) {
    $monthly = in_array($subject, $monthly_subjects);
    if ( array_key_exists($subject, $missions) ) {
    	$mission = $missions[$subject];
    	$medal = $mission['medal'];
    	$photo = $mission['photo'];
		$left = $mission['left'];
		$key = array_search($medal, $medals);

		// create array of medals done
        $medals_arr = [];
        foreach ($subject_medals[$subject] as $ord => $details) {
            $medals_arr[] = [
                'number'    => $details['missions_needed'],
                'img'       => $details['img'],
                'completed' => intval($mission['total']) >= $details['missions_needed']
            ];
//            if ($details['missions_needed'] > intval( $mission['total'] )) break;
        }

		$info[] = array( 
			'id'	=>	$subject, 
			'name'	=>	$subjectNames[$subject], 
			'medal'	=>	$medal, 
			'photo'	=>	$photo, 
            'left'	=>	$left,
            'total'	=>	intval( $mission['total'] ), 
            'needed'=>	$mission['needed'], 
            'base_amount'=>	$mission['base_amount'], 
			'next'	=>	($key === false ? 1 : ++$key),
            'nextMedalDate' => calculateNextDate($subject, $mission['needed']),
            'nextMedalImg'  => $subject_medals[$subject][$mission['ord']]['img'],
            'monthly'   => $monthly,
            'weekly'    => !$monthly,
            'medals'    => $medals_arr
		);
    } else {
    	$info[] = array( 
			'id'	=>	$subject, 
			'name'	=>	$subjectNames[$subject], 
			'medal'	=>	"None", 
			'photo'	=>	"", 
            'left'	=>	(int)$subject_medals[$subject][1]['missions_needed'],
            'total'	=>	0, 
            'needed'=>	(int)$subject_medals[$subject][1]['missions_needed'],
			'next'	=>	1,
            'nextMedalDate' => calculateNextDate($subject, $subject_medals[$subject][1]['missions_needed']),
            'nextMedalImg'  => $subject_medals[$subject][1]['img'],
            'monthly'   => $monthly,
            'weekly'    => !$monthly,
            'medals'    => [
                [
                    'number'    => (int)$subject_medals[$subject][1]['missions_needed'],
                    'img'       => $subject_medals[$subject][1]['img'],
                    'completed' => false
                ]
            ]
		);
    }
}

echo json_encode($info);
?>