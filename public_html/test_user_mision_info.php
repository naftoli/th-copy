<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
<?
require 'db.php';
$user = 8273;
$type = 'All';

//set up array depending on whether it's a regular school or a yeshiva school
if ( $type == 'All' ) {
    $subjects = array( 12, 1, 90, 42, 4, 45, 27, 13, 40, 16, 41 );
} else {
    $subjects = array( 12, 1, 4, 42, 45, 13, 40, 41 );
}

$missions = array();
$sql = "SELECT subject_id, SUM( mission_count ) as total from date_tasks_mission_marks where user_id = " . $user . " group by subject_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $subject = $row['subject_id'];
    $total = $row['total'];
	
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
		echo "Subject: " . $subject . "<br />";
		echo "Medal: " . $medal . "<br />";
		echo "Needed: " . $needed . "<br />";
		echo "Total: " . $total . "<br /><br />";
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
        WHERE medal_ord =1
        ORDER BY subject_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $medal_subjects[$row['subject_id']] = (int)$row['missions_required'];
}

//array to hold stickers info
$stickers = array();
$str = implode( ',', $subjects );
$sql = "select subject_id, subject_name, subject_image_id from subjects where subject_id in ($str)";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $stickers[$row['subject_id']][$row['subject_name']] = $row['subject_image_id'];
}

$info = array();
foreach ( $subjects as $subject ) {
    $key = array_keys( $stickers[$subject] ); 
    $info[$subject][$key[0]] = $stickers[$subject][$key[0]]; 
    if ( array_key_exists($subject, $missions) ) {
        $info[$subject][key($missions[$subject])] = $missions[$subject][key($missions[$subject])];
    } else {
        $info[$subject]["White"] = (int)$medal_subjects[$subject];
    }
}

echo "<pre>";
//print_r($info);
echo "</pre>";
?>
	</body>
</html>