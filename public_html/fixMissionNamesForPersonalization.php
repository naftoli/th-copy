<?
require 'db.php';

$subjects = array();
$sql = "select subject_id, subject_name from subjects s 
        join school_type_subjects sts using (subject_id) 
        where s.subject_type in ('', 'WWTC', 'Tanya') 
        and sts.school_type_id in (2,3,12,13) 
        group by s.subject_id 
        order by s.subject_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$subjects[] = $row['subject_id'];
}

$fixSubjects = array();
foreach ($subjects as $subject) {
	$sql = "select mission_name  
			from date_tasks_missions 
			where start_date >= 2456920 
			and end_date <= 2457276 
			and subject_id = " . $subject . " 
			and created_by_school is null 
			group by mission_name"; 
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0 && mysql_num_rows($result) < 3) {
		while ($row = mysql_fetch_assoc($result)) {
			$fixSubjects[] = $subject;
		}
	} 
}

$parshos = array();
$sql = "select * from parshos where year = 5775";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parshos[] = $row;
}

$updated = 0;
foreach ($fixSubjects as $subject) {
	foreach ($parshos as $parsha) {
		$sql = "update date_tasks_missions 
				set mission_name = '" . $parsha['name'] . "' 
				where start_date = " . $parsha['start'] . " 
				and end_date = " . $parsha['end'] . " 
				and subject_id = " . $subject;
		echo $sql . "<br />"; 
		//$result = mysql_query($sql) or die(mysql_error());
		//if ($result) {
		//	$updated += mysql_num_rows($result);
		//}
	}
}
echo "Updated: " . $updated;
?>