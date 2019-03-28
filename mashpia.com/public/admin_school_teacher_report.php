<?php
// admin_school_teacher_report - List all unique teacher names
$admin_auth = array(); 	
require('header.php'); 

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title></title>
		<link rel="alternate" media="print" href="index.php">
		<script src="jquery.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery.core.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery.ui.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery_date_time/jquery.dynDateTime.min.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery_date_time/lang/calendar-en.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" media="all" href="kiosk/scripts/jquery_date_time/css/calendar-win2k-cold-2.css"  />
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />				
		<div id="wrapper">
		<NOSCRIPT><P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P></NOSCRIPT>
	</head>
	<style>
	body{
		margin: 20px;	
		text-align:left;
	}
	.right{
		padding-right:5px;
		text-align:right;
	}
	#wrapper{
		padding: 15px;
	}
	
	table td, th{		
		padding: 3px;
	}
	.align_right{		
		text-align:right;
	}
	th{
		font-weight:bold
	}
	.small{
		font-size: 10px;
	}
	
	.student{
		background-color: #9cd4fb;
		font-size: 14px;
		border: 1px white solid;
	}

	.school{
		background-color: #b1bac9;
	}

	.product{
		background-color: #c3e3f9;
		font-size: 12px;
	}

	.sum1{
		background-color: #9fb5c4;
		font-size: 17px;
		border-bottom: 1px solid blue;
	}

	span .input{
		width:150px 
	}
	
	</style>


<h2>List of Unique teacher names for current year for each Registered school (with number of classes taught)</h2>

<a href='admin.php'>Back</a>
<br/>

	<div id="wrapper">		
	<table>
	
<?php	

$sql = "SELECT s.school_name, s.school_id, c.school_id, c.class_teacher, c.class_grade, c.class_sub, count( * ) classes_taught
		FROM classes c
		JOIN schools s ON s.school_id = c.school_id
		WHERE c.class_era = 0 and s.school_era IS NULL
		GROUP BY c.school_id, c.class_teacher
		ORDER BY s.school_name, c.class_grade, c.class_sub ";

$query = mysql_query($sql) or die(mysql_error());	

// if($from ==''){
	// exit();
// }

echo "<br/><h2></h2><br>";
echo "<table width='80%'>";
echo "<th class='student'>School Name <br>(School Id)</th>";
echo "<th class='student'>Teacher Name</th>";
echo "<th class='student'>Grade</th>";
echo "<th class='student'>Number of <br>Classes</th>";
echo "<th class='student'>Total <br>teachers</th>";
echo "</tr>";

$school_name_hold = "";
$teacher_count = 0;
$gt_teacher_count = 0;

while($row = mysql_fetch_assoc($query)){		
	
	// on new teacher
	if($school_name_hold <> $row['school_name']  && $teacher_count > 0){
		echo "<tr>";
		echo "<td class='student' colspan='4'></td><td class='student'><b>".$teacher_count."</b></td>"; 	
		echo "</tr>";
		echo "<tr><td></td></tr>";
		$teacher_count = 0  ;
	}
	
	$teacher_count ++ ;
	$gt_teacher_count ++;
	echo "<tr>";
	if($school_name_hold <> $row['school_name']){
		echo "<td class='student'>" . $row['school_name'] . " (" . $row['school_id']  . ") " . "</td>"; 		
	}
	else
		echo "<td class='student'>&nbsp;</td>"; 
	echo "<td class='student'>" . $row['class_teacher']  . "</td>"; 
	echo "<td class='student'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</td>"; 
	echo "<td class='student'>" . $row['classes_taught']  . "</td>"; 	
	echo "<td class='student'>&nbsp;</td>"; 	
	echo "</tr>";
	
	// hold	
	$school_name_hold = $row['school_name'];
}
	
//One last time
echo "<tr>";
echo "<td class='student' colspan='4'></td><td class='student'><b>".$teacher_count."</b></td>"; 	
echo "</tr>";
echo "<tr><td></td></tr>";

//Grand total
echo "<tr>";
echo "<td class='student' colspan='2'></td><td class='student'><td class='student'>Grand Total</td><td class='student'><b>".$gt_teacher_count."</b></td>"; 	
echo "</tr>";
echo "<tr><td></td></tr>";



echo "</table>";
?>