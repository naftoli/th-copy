<?php
// admin_shipping_report - Credit Card Transactions by Date/Time period showing all kits to ship by school/student.
$admin_auth = array(); 	
require('header.php'); 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Mashpia Admin - Credit Card Transactions by Date</title>
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
		max-width: 115px; /* prevents a few bad rows from making the table unreadable */
	}
	
	.student-description{
		max-width: 150px;
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
<h2>Mashpia - Credit Card Transactions by Date/Time</h2>
<a href='admin.php'>Back</a>
<br/>

	<div id="wrapper">	
	<h3>New Credit Card Transactions by Date</h3>
	<br/>
	<!--
	TODO add filter I guess?
	<form action="" method="post" >
	<table><tr>
		<td>From Date/Time: </td><td><span class="box"><input class="input" type="text" name="dateTimeCust_from" id="dateTimeCustom_from" value='<?=$default_from?>'/>	</span>	</td>
		</tr><tr>
		<td>To Date/Time: </td><td> <span class="box"><input  class="input" type="text" name="dateTimeCust_to" id="dateTimeCustom_to" value='<?=$default_to?>'/></span></td>
		</tr>
		
		<td><input type="submit" name="submit" id="submit"/></td>
	</form>  
	<table> -->
	
<?php	

$regular_items_array = array();
$regular_items_array_gt = array();
$add_on_items_array = array();
$add_on_items_array_gt = array();

$sql = " SELECT * from transactions t" .
		" LEFT JOIN schools s on s.school_id = t.school_id " .
		" ORDER by t.trans_date desc" ;		

// echo $sql;
 
$query = mysql_query($sql);	

// if($from ==''){
	// exit();
// }

echo "<br/><h2></h2><br>";
echo "<table style='table-layout:fixed;max-width:900px;'><tr>";
echo "<th class='student'>Transaction ID</th>";
echo "<th class='student'>User ID</th>";
echo "<th class='student'>School</th>";
echo "<th class='student'>Date</th>";
echo "<th class='student'>Description</th>";
echo "<th class='student'>Amount</th>";
echo "<th class='student'>Card No</th>";
echo "<th class='student'>Expiry</th>";
echo "<th class='student'>First Name</th>";
echo "<th class='student'>Last Name</th>";
echo "<th class='student'>Address</th>";
echo "</tr>";

while($row = mysql_fetch_assoc($query)){
	echo "<tr>";
	echo "<td class='student'>" . $row['trans_id']  . "</td>"; 
	echo "<td class='student'>" . $row['user_id']  . "</td>"; 
	echo "<td class='student'>" . $row['school_id'] . " " . $row['school_name'] . "</td>"; 
	echo "<td class='student'>" . $row['trans_date']  . "</td>"; 
	echo "<td class='student student-description'>" . trim($row['description']) . "</td>";
	echo "<td class='student'>" . $row['amount']  . "</td>"; 
	echo "<td class='student'>" . substr($row['card_number'],0,15)  . "****</td>"; 
	echo "<td class='student'>" . $row['expiry_date']  . "</td>"; 
	echo "<td class='student'>" . $row['first']  . "</td>"; 
	echo "<td class='student'>" . $row['last']  . "</td>"; 
	echo "<td class='student'>" . $row['address'] . ", " .$row['state'] . ", " . $row['zip']  . "</td>"; 	
	echo "</tr><tr>";
	echo "<td colspan=12 class='student'><b>Response: </b>" . trim($row['response'])  . "</td>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "</tr>";
}
echo "</table>";
?>