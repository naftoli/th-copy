<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title></title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
	</head>
	<style>
	body{
		margin: 20px;	
		text-align:left;
	}
	table td, th{
		border: 2px solid blue;				
		padding: 3px;
	}
	.align_right{		
		text-align:right;
	}
	th{
		font-weight:bold
	}
	.small
	{
		font-size: 10px;
	}
	
	</style>
	<body>
	
	<br><br>	
	<a href='sales_report_01_export.php'>Click to generate report in Excel format</a>
	<br><br>	
<?php
//include("check_admin_id.php");

include("db.php");
$admin_id = $_COOKIE['admin_id'];


	
print_r($header);

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// summary version for screen
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$sql = "SELECT 
s.school_name, 
c.class_grade,
sum(u.add_on_one) as add_on_one,
sum(u.add_on_two) as add_on_two,
sum(user_registration_fee) as total_fees,
count(u.user_id) as count
FROM users u
JOIN schools s ON s.school_id = u.school_id
JOIN classes c ON c.class_id = u.class_id
AND c.school_id = u.school_id
WHERE user_registered  IS NOT NULL
GROUP BY 
s.school_name, 
c.class_grade
ORDER by 
s.school_name,
c.class_grade ";

$query = mysql_query($sql);	

echo "<h2>Mashpia - This years's orders</h2>";
echo "<br>";
echo "<table><tr>";
echo "<th>" . "School" . "</th>";
echo "<th>" . "Grade" . "</th>";
echo "<th>" . "Total $" . "</th>";
echo "<th>" . "Add-on one" . "</th>";
echo "<th>" . "Add-on two" . "</th>";
echo "<th>" . "Total Registered<br><span class='small'>a student is considered to <br>be registered if student record <br> has a non-blank registration date</span>" . "</th>";
echo "<th>" . "School Total" . "</th>";
echo "</tr>";

while($row = mysql_fetch_assoc($query)){

	if ($row['school_name'] != $school_hold  && $school_hold != ''){
		echo "<tr><td colspan=6></td>";
		echo "<td class='align_right'><b>";		
		printf("%1\$.0f",$school_total);
		$school_total = 0;
		echo "</b></td></tr>";
	}
	
	$school_total += $row['count'];
	$school_g_total += $row['count'];
	
	echo "<tr><td>" ;
	echo $row['school_name'] ;
	echo "</td><td>" ;
	echo $row['class_grade'] ;
	echo "</td><td class='align_right'>" ;
	printf("%1\$.2f",$row['total_fees']);
	echo "</td><td class='align_right'>" ;
	printf("%1\$.0f",$row['add_on_one']);
	echo "</td><td class='align_right'>" ;
	printf("%1\$.0f",$row['add_on_two']);
	echo "</td><td class='align_right'>" ;
	printf("%1\$.0f",$row['count']);	
	echo "</td><td>" ;	
	echo "</td>" ;
	echo "</tr>" ;
	
	$school_hold = $row['school_name'];
}	
		// last line
		echo "<tr><td colspan=6></td>";
		echo "<td class='align_right'><b>";		
		printf("%1\$.0f",$school_total);		
		echo "</b></td></tr>";

echo "<tr><td colspan=7></b></td></tr>";
echo "<tr><td colspan=6><b>Grand Total</b></td>";
echo "<td class='align_right'><b>";		
printf("%1\$.0f",$school_g_total);
$school_total = 0;
echo "</b></td></tr>";


?>
