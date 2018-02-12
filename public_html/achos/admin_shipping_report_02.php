<?php
// admin_shipping_report - Registrations by Date/Time period showing all kits to ship by school/student.
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
	
	#wrapper{
		padding: 15px;
	}
	
	table td, th{
		x_border: 2px solid blue;				
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

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#dateTimeCustom_from").dynDateTime({
				showsTime: true,
				ifFormat: "%Y-%m-%d %H:%M:%S",
				daFormat: "%l;%M %p, %e %m,  %Y"
			});
		
			jQuery("#dateTimeCustom_to").dynDateTime({
				showsTime: true,
				ifFormat: "%Y-%m-%d %H:%M:%S",
				daFormat: "%l;%M %p, %e %m,  %Y"
			});

			$('#submit').click(function() {
				date_from = $('#dateTimeCustom_from').val();
				date_to = $('#dateTimeCustom_to').val();
				var from_Date = new Date(date_from);
				var to_Date = new Date(date_to);				
			});
		});
		
	</script>

<?php	
// get passed in variables
$from = isset($_POST['dateTimeCust_from']) 	? $_POST['dateTimeCust_from'] : ''; 
$to = isset($_POST['dateTimeCust_to']) 	? $_POST['dateTimeCust_to'] : ''; 

// from day
if($from<>'') $default_from = $from;
else $default_from = 'YYYY/MM/DD HH:MM:SS';

// to day
if($to<>'') $default_to = $to;
else  $default_to = 'YYYY/MM/DD HH:MM:SS';

?>	
<h2>Mashpia - Registrations by Date/Time</h2>
<a href='admin.php'>Back</a>
<br/>

	<div id="wrapper">	
	<h3>New Registrations by Date</h3>
	<br/>
	<form action="" method="post" >
	<table><tr>
		<td>From Date/Time: </td><td><span class="box"><input class="input" type="text" name="dateTimeCust_from" id="dateTimeCustom_from" value='<?=$default_from?>'/>	</span>	</td>
		</tr><tr>
		<td>To Date/Time: </td><td> <span class="box"><input  class="input" type="text" name="dateTimeCust_to" id="dateTimeCustom_to" value='<?=$default_to?>'/></span></td>
		</tr>
		
		<td><input type="submit" name="submit" id="submit"/></td>
	</form>
	<table>
	
<?php	

$regular_items_array = array();
$regular_items_array_gt = array();
$add_on_items_array = array();
$add_on_items_array_gt = array();

$sql = "SELECT u.user_registered, 
			   u.first,
			   u.child_type_id,
			   u.last,			   
			   u.add_on_one,
			   u.add_on_two,
			   u.user_registration_fee,
			   u.shirt_size,
			   u.user_start_date,
			   u.user_id,
			   u.cc_ref,
			   s.school_name,	
			   c.class_grade,   
			   IF (c.class_grade IN ('3', '4', '5', '6', '7','8','9'), 'old', 'young') AS age
		FROM users u 
		LEFT JOIN schools s on 
				s.school_id =  u.school_id
		LEFT JOIN classes c on 
				c.class_id  = u.class_id and
				c.school_id  = u.school_id 
		WHERE user_registered >=  '".  $from  . "' 
			  and user_registered <  '".  $to  . "' 			  
		ORDER by u.user_registered desc , u.last, u.first " ;
		
//echo $sql;
 
$query = mysql_query($sql);	

if($from ==''){
	exit();
}

echo "<br/><h2></h2><br>";
echo "<table width=90%>";
echo "<th class='student'>Date/Time</th>";
echo "<th class='student'>User name</th>";
echo "<th class='student'>School</th>";
echo "<th class='student'>Grade</th>";
echo "<th class='student'>First Registration Date</th>";
echo "<th class='student'>Fee paid</th>";
echo "<th class='student'>Credit Card Reference</th>";
echo "<th class='student'>Add on one</th>";
echo "<th class='student'>Add on two</th>";
echo "<th class='student'>Shirt Size</th>";
echo "</tr>";
while($row = mysql_fetch_assoc($query)){
	echo "<tr class='student'>";
	echo "<td class='student'>" . $row['user_registered']  . "</td>";
	echo "<td class='student'>" . $row['last'] .", ". $row['first']	. " (" . $row['user_id'] . ")</td>";	
	echo "<td class='student'>" . $row['school_name']  . "</td>";
	echo "<td class='student'>" . $row['class_grade']  . "</td>";
	echo "<td class='student'>" . date('Y-m-d', strtotime(jdtogregorian($row['user_start_date'])))  . "</td>";	
	echo "<td class='student'>" . $row['user_registration_fee']  . "</td>";	
	echo "<td class='student'>" . $row['cc_ref']  . "</td>";	
	echo "<td class='student'>" . $row['add_on_one']  . "</td>";
	echo "<td class='student'>" . $row['add_on_two']  . "</td>";
	echo "<td class='student'>" . $row['shirt_size']  . "</td>";
	echo "</tr>";
}
echo "</table>";
			
