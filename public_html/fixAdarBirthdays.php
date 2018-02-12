<?
require_once 'db.php';

//find and delete all adar birthdays
$missionIDs = array();
$sql = "select distinct dtm.date_tasks_mission_id from date_tasks_missions dtm 
		join date_tasks dt using (date_tasks_mission_id) 
		where dtm.start_date >= 2456690 
		and dtm.end_date <= 2456778 
		and dt.cat = 'Birthday'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$missionIDs[] = $row['date_tasks_mission_id'];
}

foreach ($missionIDs as $id) {
	mysql_query("delete from date_tasks where date_tasks_mission_id = " . $id);
	mysql_query("delete from date_tasks_missions where date_tasks_mission_id = " . $id);
}

//find all users with birthdays in adar
$users = array();
$sql = "select * from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[$row['user_id']] = $row;
}

$adarUsers = array();
foreach ($users as $user_id => $user) {
	if (!empty($user['dob'])) {
		$dob = $user['dob'];
		$arrDOB = explode('-', $dob);
        //check that dob makes sense
        $yy = $arrDOB[0];
		$mm = $arrDOB[1];
		$dd = $arrDOB[2];
        if ($yy > date('Y') || $yy < (date('Y') - 15) || $mm == 0 || $dd == 0) {
            continue;
        } 
        //check if dob_he should be one day further
        if ($user['dob_he_offset']) {
            //add one to dob
            $date = new DateTime( $dob );
            $date->add( new DateInterval( 'P1D' ) );
            $newDate = $date->format( 'Y-m-d' );
            $arrDOB = explode('-', $newDate);
        }                   
        $jd = gregoriantojd($arrDOB[1], $arrDOB[2], $arrDOB[0]);
		$jDate = jdtojewish($jd);
		$arrJDate = explode("/", $jDate);
		if (in_array($arrJDate[0], array(6,7,8))) {
			$adarUsers[] = $user_id;
		}
	}
}

//create adar birthdays
require_once 'class.birthday.php';
foreach ($adarUsers as $id) {
	$b = new Birthday($id);
	$b->setBirthday();
	$errors = $b->getErrors();
	if ($errors) {
		echo "Number of Errors: " . count($errors) . "<br />";
	    echo "<pre>";
	    print_r($errors);
	    echo "</pre>";
	}
}
?>