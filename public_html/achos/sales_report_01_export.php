<?php
$filename = "salesreport_" . date('Ymd_His') . ".csv";
header("Content-Type: application/csv");
header("Content-Disposition: attachment;Filename=$filename");
include("db.php");
$admin_id = $_COOKIE['admin_id'];

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// summary version for screen
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$sql = 
	"SELECT
	s.school_name,	
	c.class_grade, 
	u.class_id,
	u.last, 
	u.first, 
	u.user_id, 
	u.user_registered , 
	u.fee_id,
	u.user_registration_fee, 
	u.add_on_one,
	u.add_on_two,
	s.package_id,
	c.class_teacher
	FROM users u
	JOIN schools s on s.school_id =  u.school_id
	JOIN classes c on 
				 c.class_id  = u.class_id and
				 c.school_id  = u.school_id 
	WHERE user_registered  IS NOT NULL
	order by 
	s.school_name,
	c.class_grade,
	u.last, 
	u.first ";
$query = mysql_query($sql);	


$header = array(
	school_name,
	class_grade, 
	class_id,
	last_name, 
	first_name, 
	user_id, 
	date_registered , 
	fee_type,
	fee_in_dollars, 
	add_on_one,
	add_on_two,
	package_id,
	class_teacher);

$outstr.= join(',', $header)."\n";	
	
 while($row = mysql_fetch_assoc($query)){
	$outstr.= join(',', $row)."\n";
}
echo $outstr;

?>
