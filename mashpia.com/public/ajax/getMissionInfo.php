<?
require '../db.php';
$user = $_GET['user_id'];
// $type = $_GET['type'];

$subjects = [ 1, 4, 12, 13, 16, 21, 27, 40, 41, 42, 45, 90, 100 ];
// find out user type
$sql = "select school_type_id from users where user_id = " . $user;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
switch ( $row['school_type_id'] ) {
    case 12: case 13:
        $subjects = [ 1, 4, 21, 27, 41, 42, 45, 90, 92, 93, 94, 100 ];
        break;
    case 4: case 5:
        $subjects = [ 121, 122, 123 ];
        break;
}

if($_GET['debug']){
	// log errors to the page
	//error_reporting(E_ALL);
	ini_set("display_errors", 1);
	$debug = true;
}
 
$missions = [];
$sql = "SELECT subject_id, mark_date, SUM( mission_count ) AS total 
		FROM user_tracks ut
		LEFT JOIN date_tasks_mission_marks dtmm
		USING ( user_id, subject_id ) 
		WHERE ut.user_id = $user 
		AND ut.enrolled = 1 
        AND subject_id in (" . implode(',', $subjects) . ") 
		GROUP BY subject_id";
if($debug) echo $sql."\n";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	if($debug) print_r($row);
    $subject = $row['subject_id'];
    $total = $row['mark_date'] ? $row['total'] : 0; // take care of the sql query returning 1 when there are no marks
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
    $sql2 = "select medal_name, missions_required from medals_subjects 
             join medals using (medal_ord)    
             where subject_id = " . $subject . " 
             order by medal_ord";    
    $result2 = mysql_query( $sql2 );
    $needed = 0;
    while ( $row2 = mysql_fetch_assoc( $result2 ) ) {
        $medal = $row2['medal_name'];
        $required = (int)$row2['missions_required'];
        $needed += $required;
        if ( $needed > $total ) {
            $missions[$subject][$medal] = $needed - $total; 
            break;
        }
    }   
}

//array to hold required missions to reach first medal for each subject
$medal_subjects = array();
$sql = "SELECT subject_id, missions_required
        FROM medals_subjects
        WHERE medal_ord = 1 
        AND subject_id in (" . implode(',', $subjects) . ") 
        ORDER BY subject_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $medal_subjects[$row['subject_id']] = (int)$row['missions_required'];
}

//array to hold stickers info
$stickers = [];
$sql = "select subject_id, subject_name, subject_image_id from subjects where subject_id in (" . implode(',', $subjects) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	if ($row['subject_id'] == 1) 
		$row['subject_name'] = 'תהילים';
    $stickers[$row['subject_id']][$row['subject_name']] = $row['subject_image_id'];
}

$info = [];
foreach ( $subjects as $subject ) {
    $key = array_keys( $stickers[$subject] ); 
    $info[$subject][$key[0]] = $stickers[$subject][$key[0]]; 
    if ( array_key_exists($subject, $missions) ) {
        $info[$subject][key($missions[$subject])] = $missions[$subject][key($missions[$subject])];
    } else {
        $info[$subject]["White"] = (int)$medal_subjects[$subject];
    }
}

echo json_encode($info);
?>