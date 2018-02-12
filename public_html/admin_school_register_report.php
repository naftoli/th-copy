<?php
// admin_school_register_report - List all registered Schools and registered students.
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
<h2>Mashpia - Registered Schools and Registered Students</h2>

<a href='admin.php'>Back</a>
<br/>

	<div id="wrapper">	
	<h3>Registered Schools</h3>
	<br />
	
<?php	

// -----------------------------------------------------
// show list of Schools
// -----------------------------------------------------

//$sql = "SELECT * from schools ORDER by school_era ASC, last_register_date ASC, school_id ASC" ;		

$sql = "SELECT s.* , 
		(SELECT count( * )
		FROM users u
		WHERE s.school_id = u.school_id) as student_total,

		(SELECT count( * )
		FROM users u2
		WHERE s.school_id = u2.school_id
		and (u2.user_registered is not null and u2.user_registered > 0)) as registered_student_total, 
		
		(select count(*) 
		from users u3 
		where s.school_id = u3.school_id 
		and (u3.user_registered is null or u3.user_registered = 0)) as unregistered_student_total 
	
		FROM schools s 
		WHERE s.school_era is null
		AND s.chayolei = 1
		AND s.school_id != 82 
		ORDER by school_name" ;		
	   
//echo $sql;
 
$query = mysql_query($sql);	

// if($from ==''){
	// exit();
// }

echo "<table width='95%'>";
echo "<th class='student'>Last Register Date</th>";
echo "<th class='student'>School ID</th>";
echo "<th class='student'>Name</th>";
echo "<th class='student'>City,State</th>";
echo "<th class='student'>Shipping Method</th>";
echo "<th class='student'>Gender</th>";
echo "<th class='student'>Credit Card Name</th>";
echo "<th class='student'>Total Students</th>";
echo "<th class='student'>Total Registered</th>";
echo "<th class='student'>Total Unregistered</th>";
echo "</tr>";

$student_grand_total = 0;
$registered_student_grand_total = 0;
$unregistered_student_grand_total = 0;

while($row = mysql_fetch_assoc($query)){		
	echo "<tr>";
	echo "<td class='student'>" . $row['last_register_date']  . "</td>"; 	
	echo "<td class='student'>" . $row['school_id']  . "</td>"; 
	echo "<td class='student'>" . $row['school_name']  . "</td>"; 
	echo "<td class='student'>" . $row['school_city'] .", " . $row['school_state']  . "</td>"; 		
	echo "<td class='student'>" . $row['shipping_method']  . "</td>"; 	 	
	echo "<td class='student'>" . $row['school_gender']  . "</td>"; 	
	echo "<td class='student'>" . $row['cc_first'] ." " . $row['cc_last']  . "</td>"; 	
	echo "<td class='student right'>" . $row['student_total']  . "</td>"; 										    	
	echo "<td class='student right'>" . $row['registered_student_total'] . "</td>"; 	
	echo "<td class='student right'>" . $row['unregistered_student_total'] . "</td>"; 
	echo "</tr>";
	
 $student_grand_total 				+= $row['student_total'];
 $registered_student_grand_total 	+= $row['registered_student_total'];
 $unregistered_student_grand_total  += $row['unregistered_student_total'];
	
}
echo "<td class='student' colspan='7'></td>"; 
echo "<td class='student right'>" . $student_grand_total . "</td>"; 
echo "<td class='student right'>" . $registered_student_grand_total . "</td>"; 
echo "<td class='student right'>" . $unregistered_student_grand_total . "</td>"; 

echo "</table>";

//show unregistered schools
echo "<br /><br /><h3>Unregistered Schools</h3><br />";

$sql = "SELECT s.* , 
		(SELECT count( * )
		FROM users u
		WHERE s.school_id = u.school_id) as student_total,

		(SELECT count( * )
		FROM users u2
		WHERE s.school_id = u2.school_id
		and (u2.user_registered is not null and u2.user_registered > 0)) as registered_student_total, 
		
		(select count(*) 
		from users u3 
		where s.school_id = u3.school_id 
		and (u3.user_registered is null or u3.user_registered = 0)) as unregistered_student_total 
	
		FROM schools s 
		WHERE s.school_era is not null
		AND s.chayolei = 1
		AND s.school_id != 82 
		ORDER by school_name" ;		
	   

//echo $sql;
 
$query = mysql_query($sql);	

// if($from ==''){
	// exit();
// }

echo "<table width='95%'>";
echo "<th class='student'>Last Register Date</th>";
echo "<th class='student'>School ID</th>";
echo "<th class='student'>Name</th>";
echo "<th class='student'>City,State</th>";
echo "<th class='student'>Shipping Method</th>";
echo "<th class='student'>Gender</th>";
echo "<th class='student'>Credit Card Name</th>";
echo "<th class='student'>Total Students</th>";
echo "<th class='student'>Total Registered</th>";
echo "<th class='student'>Total Unregistered</th>";
echo "</tr>";

$student_grand_total = 0;
$registered_student_grand_total = 0;
$unregistered_student_grand_total = 0;

while($row = mysql_fetch_assoc($query)){		
	echo "<tr>";
	echo "<td class='student'>" . $row['last_register_date']  . "</td>"; 	
	echo "<td class='student'>" . $row['school_id']  . "</td>"; 
	echo "<td class='student'>" . $row['school_name']  . "</td>"; 
	echo "<td class='student'>" . $row['school_city'] .", " . $row['school_state']  . "</td>"; 		
	echo "<td class='student'>" . $row['shipping_method']  . "</td>"; 	 	
	echo "<td class='student'>" . $row['school_gender']  . "</td>"; 	
	echo "<td class='student'>" . $row['cc_first'] ." " . $row['cc_last']  . "</td>"; 	
	echo "<td class='student right'>" . $row['student_total']  . "</td>"; 										    	
	echo "<td class='student right'>" . $row['registered_student_total'] . "</td>"; 	
	echo "<td class='student right'>" . $row['unregistered_student_total'] . "</td>"; 	
	echo "</tr>";
	
 $student_grand_total 				+= $row['student_total'];
 $registered_student_grand_total 	+= $row['registered_student_total'];
 $unregistered_student_grand_total  += $row['unregistered_student_total'];
	
}
echo "<td class='student' colspan='7'></td>"; 
echo "<td class='student right'>" . $student_grand_total . "</td>"; 
echo "<td class='student right'>" . $registered_student_grand_total . "</td>"; 
echo "<td class='student right'>" . $unregistered_student_grand_total . "</td>"; 

echo "</table>";


// -----------------------------------------------------
// show list of administrators with username, password
// -----------------------------------------------------

$sql = "SELECT s.*, a.*,  aa.*,
		(SELECT count( * )
		FROM users u
		WHERE s.school_id = u.school_id) as student_total,

		(SELECT count( * )
		FROM users u2
		WHERE s.school_id = u2.school_id
		and u2.user_registered <> 'NULL') as registered_student_total
	
		FROM schools s
		
		LEFT JOIN admin_auths aa on aa.id = s.school_id
		
		LEFT JOIN admins a on a.admin_id = aa.admin_id
				
		ORDER by school_name ASC";
	   

//echo $sql;
 
$query = mysql_query($sql);	

echo "<br/><h2>List of Supervisors by school</h2><br>";
echo "<table width='95%'>";
echo "<th class='student'>School Year</th>";
echo "<th class='student'>Last Register Date</th>";
echo "<th class='student'>School ID</th>";
echo "<th class='student'>Name</th>";
echo "<th class='student'>City,State</th>";
echo "<th class='student'>Role ID</th>";
echo "<th class='student'>Supervisor</th>";
echo "<th class='student'>user/pass</th>";
echo "</tr>";

$student_grand_total = 0;
$registered_student_grand_total = 0;

while($row = mysql_fetch_assoc($query)){		
	echo "<tr>";
	echo "<td class='student'>" . $row['school_name']  . "</td>"; 
	echo "<td class='student'>" . $row['school_era']  . "</td>"; 
	echo "<td class='student'>" . $row['last_register_date']  . "</td>"; 	
	echo "<td class='student'>" . $row['school_id']  . "</td>"; 
	echo "<td class='student'>" . $row['school_city'] .", " . $row['school_state']  . "</td>"; 			
	echo "<td class='student'>" . $row['role_id'] ."</td>"; 	
	echo "<td class='student'>" . $row['first']  ." ".  $row['last'] . "</td>"; 	
	echo "<td class='student'>" . $row['username']  ." / ".  $row['password']  . "</td>"; 		
	echo "</tr>";
	
 $student_grand_total 				+= $row['student_total'];
 $registered_student_grand_total 	+= $row['registered_student_total'];
	
}
echo "<td class='student' colspan='10'></td>"; 
echo "<td class='student right'>" . $student_grand_total . "</td>"; 
echo "<td class='student right'>" . $registered_student_grand_total . "</td>"; 

echo "</table>";
?>