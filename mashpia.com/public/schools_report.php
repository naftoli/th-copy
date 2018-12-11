<?php
$admin_auth = array('school'); 
require('header.php'); 

include("classes/school.php");
$schools = array();
$sql = "SELECT * FROM schools WHERE school_era IS NULL ORDER BY shipping_method, school_name";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$school = new \classes\school($row);
	$school->get_number_of_registered_students();
	$school->get_number_of_teachers();
	array_push($schools, $school);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">


<HTML>

	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Schools Report - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>

	<BODY>
		<? include('admin_header.php'); ?>
		
		<DIV class="body">
			<H1>Schools Report</H1>
			
			<TABLE class="pretty_grid">
				<THEAD>
					<TR>
						<TH>Name</TH>
						<TH>Address</TH>
						<TH>Pickup</TH>
						<TH>Teachers</TH>
						<TH>Students</TH>
						<th>Total</th>
					</TR>
				</THEAD>
				
				<? foreach ($schools as $school) : ?>
				<TR>
					<TD><?=$school->school_name;?></TD>
					<TD><?=$school->shipping_address1;?> <?=$school->shipping_address2;?> <?=$school->school_city;?> <?=$school->school_state;?> <?=$school->school_country;?></TD>
					<TD><?=$school->shipping_method;?></TD>		
					<TD style="text-align:right;"><?=$school->number_of_teachers;?></TD>
					<TD style="text-align:right;"><?=$school->number_of_registered_students;?></TD>
					<td style="text-align:right;"><?=$school->number_of_teachers + $school->number_of_registered_students;?></td>
				</TR>
				<? endforeach; ?>				
			</TABLE>
		</DIV>
	</BODY>
	
</HTML>
