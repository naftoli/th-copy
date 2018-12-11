<?
require 'db.php';

$types = array(12,13);
$subjects = array(92,93);
foreach ($types as $type) {
	foreach ($subjects as $subject) {
		$sql = "insert into school_type_subjects 
				set school_type_id = $type, 
				subject_id = $subject";
		mysql_query($sql);
	}
}