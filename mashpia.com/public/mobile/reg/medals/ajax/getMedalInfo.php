<?
// load up the database
require_once( dirname(__FILE__) . '/../../../../db.php' );
$user = mysql_real_escape_string( $_POST['user_id'] );

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
    $sql2 = "SELECT medal_name, missions_required, profile_photo_id FROM medals_subjects 
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
        $tempMedals[$ctr] = $row2['medal_name'];
        if ( $row2['profile_photo_id'] ) $tempMedalPics[$ctr] = $row2['profile_photo_id'];
        $required = (int)$row2['missions_required'];
        $base_amount = $needed;
        $needed += $required;

        if ( $needed > $total ) {
        	if ($ctr == 0) {
        		$missions[$subject] = array(
	            	'medal'	=>	"None", 
	            	'photo'	=>	"", 
                    'left'	=>	$needed - $total,
                    'total' =>  $total,
                    'needed' => $needed,
                    'base_amount' => $base_amount
				);
        	} else {
	            $missions[$subject] = array(
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
$medal_subjects = array();
$sql = "SELECT subject_id, missions_required, profile_photo_id 
        FROM medals_subjects
        WHERE medal_ord = 1 
        ORDER BY subject_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $medal_subjects[$row['subject_id']] = array(
    	'photo'	=>	$row['profile_photo_id'],
		'left'	=>	$row['missions_required']
	);
}

$medals = array();
$sql = "select medal_ord, medal_name from medals";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$medals[$row['medal_ord']] = $row['medal_name'];
}

foreach ( $subjects as $subject ) {
    if ( array_key_exists($subject, $missions) ) {
    	$mission = $missions[$subject];
    	$medal = $mission['medal'];
    	$photo = $mission['photo'];
		$left = $mission['left'];
		$key = array_search($medal, $medals);
		$info[] = array( 
			'id'	=>	$subject, 
			'name'	=>	$subjectNames[$subject], 
			'medal'	=>	$medal, 
			'photo'	=>	$photo, 
            'left'	=>	$left,
            'total'	=>	intval( $mission['total'] ), 
            'needed'=>	$mission['needed'], 
            'base_amount'=>	$mission['base_amount'], 
			'next'	=>	($key === false ? 1 : ++$key)
		);
    } else {
    	$info[] = array( 
			'id'	=>	$subject, 
			'name'	=>	$subjectNames[$subject], 
			'medal'	=>	"None", 
			'photo'	=>	"", 
            'left'	=>	(int)$medal_subjects[$subject]['left'],
            'total'	=>	0, 
            'needed'=>	(int)$medal_subjects[$subject]['left'],
			'next'	=>	1
		);
    }
}

echo json_encode($info);
?>