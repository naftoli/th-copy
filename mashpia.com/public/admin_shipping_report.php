<?php
// admin_shipping_report - Registrations by Date/Time period showing all kits to ship by school/student.
$admin_auth = array(); 	
require('header.php'); 
//error_reporting( E_ERROR | E_USER_ERROR | E_WARNING );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Mashpia.com - Registrations by Date/Time</title>
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
	.small
	{
		font-size: 10px;
	}
	
	.student{
		background-color: #9cd4fb;
		font-size: 12px;
		border-bottom: 2px solid black;
		border-top: 2px solid black;
	}

	.school{
		background-color: #b1bac9;
		page-break-before: always;

	}

	.product{
		background-color: #c3e3f9;
		font-size: 12px;
	}

	
	table.sum1
	{		
	}
	
	.sum1{
		background-color: #9fb5c4;
		font-size: 17px;
		border-bottom: 2px solid black;
		border-top: 2px solid black;
	}

	.begin_totals
	{
		margin-top:30px;
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
$detail_level = isset($_POST['detail_level']) 	? $_POST['detail_level'] : ''; 

// detail level
if ($detail_level == 'Details') {	$show_student = true; $det_sel ='selected' ;} else {$det_sel='';}
if ($detail_level == 'Summary') {	$show_student = false; $sum_sel ='selected' ;} else {$sum_sel='';}

// from day
if($from<>'') $default_from = $from;
else $default_from = 'YYYY/MM/DD HH:MM:SS';

// to day
if($to<>'') $default_to = $to;
else  $default_to = 'YYYY/MM/DD HH:MM:SS';

?>	
<h2>Mashpia - Shipping Report</h2>
<a href='admin.php'>Back</a>
<br/>

	<div id="wrapper">	
	<h3>Registrations by Date/Time period showing all kits to ship by school/student.</h3>
	<br/>
	<form action="" method="post" >
	<table><tr>
		<td>From Date/Time: </td><td><span class="box"><input class="input" type="text" name="dateTimeCust_from" id="dateTimeCustom_from" value='<?=$default_from?>'/>	</span>	</td>
		</tr><tr>
		<td>To Date/Time: </td><td> <span class="box"><input  class="input" type="text" name="dateTimeCust_to" id="dateTimeCustom_to" value='<?=$default_to?>'/></span></td>
		</tr>
		<td>
		<select name='detail_level'>
		<option <?=$sum_sel?> >Summary</option>
		<option <?=$det_sel?> >Details</option>
		</select>
		</td>
		<td><input type="submit" name="submit" id="submit"/></td>
	</table>
	</form>
	
<?php	

$regular_items_array = array();
$regular_items_array_gt = array();
$add_on_items_array = array();
$shirt_size_array = array();
$add_on_items_array_gt = array();
$student_count = 0;

$sql = "SELECT u.user_registered, 
			u.first,
			u.child_type_id,
			u.last,			   
			u.add_on_one,
			u.add_on_two,
			u.user_start_date,
			u.user_id,
			u.shirt_size,
			s.school_name,	
			s.shipping_first, 
			s.shipping_last, 	 
			s.shipping_phone, 	 
			s.shipping_address1, 
			s.shipping_address2,  
			s.shipping_city,  
			s.shipping_state,  
			s.shipping_postal,  
			s.shipping_country,  
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
		ORDER by s.school_name, age, u.last " ;
		
//echo $sql;
 
$query = mysql_query($sql);	
$school_name_hold='';

if($detail_level ==''){
	exit();
}

// get all schools
echo "<br/><h2>Totals by School</h2><br>";
while($row = mysql_fetch_assoc($query)){
			
	// first registration (user_start_date)
	$first_registered = date('Y-m-d', strtotime(jdtogregorian($row['user_start_date'])));
	
	// latest registratin (user_registered)
	$latest_registration = substr($row['user_registered'],0,10);
	
	// is first time registration
	$first_time_reg = '0';
	if($first_registered == $latest_registration){
		$first_time_reg = '1';
	}
	
	// determine if young or old age group
	if ($row["class_grade"] > "3"  && $row["class_grade"] < "9" )
		$age = "old";
	else
		$age = "young";
		
	$student_count++;	
	
	// on new school
	if ($school_name_hold <> $row["school_name"]) {		
			$student_count= 1 ;
			// print school totals			
			print_school_totals($regular_items_array,$add_on_items_array);
			//reset arrays 
			$regular_items_array = array();
			$add_on_items_array = array();		
			// new school name
			echo "<br><br><h3 class='school' >School: " . $row['school_name'] ." </h3>\n" ;			
			echo "Attention: " . $row['shipping_first'] . " " . $row['shipping_last'] . "<br>";			
			echo "Phone:" . $row['shipping_phone'] ."<br>";
			echo $row['shipping_address1'] . "," . $row['shipping_address2'] ."<br>";
			echo $row['shipping_city'] . ", ";
			echo $row['shipping_state'] . ", ";
			echo $row['shipping_postal'] . "<br>";
			echo $row['shipping_country'] . "<br><br><br>";
		}
			
		// hold old values
		$school_name_hold = $row["school_name"];					
		
		if ($show_student)
		{
			echo "<table class='student' width=100%>\n";	
			echo "<tr><td width=5%></td>\n";				
			echo "<td width=15%>" . $student_count . ". " . $row["last"] . ", " . $row["first"] . "</td>\n";		
			echo "<td width=30%>" . $row["school_name"] . ", Grade: " . $row["class_grade"] ."</td>\n";
			//echo "<td> Grade:" . $row["class_grade"] ."-" . $age . "</td>\n";				
			echo "<td width=30%>Registration Date: " . $row['user_registered'] . "<br>  First Registered:" .  $first_registered . "</td>\n";				
			echo "<td width=10%> T" . $row["child_type_id"] . " / ". $row["user_id"]  ."</td>\n";	
			echo "</tr>\n";
			echo "</table>\n";	
		}
		
		// get REGULAR items to be delievered
		get_regular_items($row['child_type_id'],$age,$first_time_reg);	
		
		// get ADD-ON items to be delievered
		get_add_on_items($row['add_on_one'], $row['add_on_two'],$row['shirt_size']);			
		}

	print_school_totals($regular_items_array,$add_on_items_array);

	echo "<p class='school'><br/><h2>Grand Totals - all schools </h2><br>";

	// Print Grand totals
	print_school_totals($regular_items_array_gt,$add_on_items_array);
	print_shirt_totals();

	// ----------------------------------------------------------------------------------------------------
	// function - get regular items to be delivered
	// ----------------------------------------------------------------------------------------------------
	function get_regular_items($child_type_id, $age, $first_time_reg)	
	{
		global $show_student;
		global $regular_items_array;
		global $regular_items_array_gt;
		$sql = "SELECT * from packages p " .
				" JOIN items i on  i.item_id = p.item_id  " .
				" WHERE p.child_type_id ='"  . $child_type_id . "'" .
					" and (user_age = '" . $age . "'  or user_age = 'all') " .
					" and (i.first_time_only = '0' or ( i.first_time_only = '1' and " . $first_time_reg . " = '1')) " .
					" and i.is_active = '1' " .
					" and p.is_active = '1' " ;
	
		$query = mysql_query($sql);	
		while($row = mysql_fetch_assoc($query)){
			if ($show_student){	
				echo "<table width=100% class='product'><tr><td  class='product' width=10%></td><td  class='product'>" . $row['description'] . " (". $row['user_age'] .  ")</td></tr><table>";		
			}			
			
			// if (isset($regular_items_array[$row['description']])) {
				// $regular_items_array[$row['description']] = 0 ;
			// }
			// if (isset($regular_items_array_gt[$row['description']])) {
				// $regular_items_array_gt[$row['description']] = 0 ;
			// }			
			
			@$regular_items_array[$row['description']] = $regular_items_array[$row['description']] +1 ;
			@$regular_items_array_gt[$row['description']] = $regular_items_array_gt[$row['description']] +1 ;
		}
	}	
	
	// ----------------------------------------------------------------------------------------------------
	// function - get add on items
	// ----------------------------------------------------------------------------------------------------
	function get_add_on_items($add_on_one, $add_on_two,$shirt_size)
	{
		global $add_on_items_array;		
		global $show_student;
		global $shirt_size_array;
	
		if ($add_on_one == '1')
		{
			@$add_on_items_array['Student Add-On Option 1'] = $add_on_items_array[$row['add_on_one']] +1 ;
			if ($show_student){
				echo "<table width=100% class='product'><tr class='product'><td  class='product' width=10%></td>" ;
				echo "<td class='product'>Student Add-On Option 1 (". $shirt_size .")</td></tr><table>"; 
				@$shirt_size_array[$shirt_size] = $shirt_size_array[$shirt_size] + 1;
			}
		}
		if ($add_on_two == '1')
		{
			@$add_on_items_array['Student Add-On Option 2'] = $add_on_items_array[$row['add_on_two']] +1 ;
			if ($show_student){ echo "<table width=100% class='product'><tr class='product'><td  class='product' width=10%></td><td  class='product'>Student Add-On Option 2</td></tr><table>"; }
		}		
	}	
	
	// ----------------------------------------------------------------------------------------------------
	// function - print regular array totals
	// ----------------------------------------------------------------------------------------------------
	
	function print_school_totals($regular_items_array,$add_on_items_array)
	{
		if ($regular_items_array)
		{
			echo "<p class='begin_totals'>School Totals:</p>";
		}
		echo "<table class='sum1' width=100%>\n";
		foreach ($regular_items_array as $key => $value){			
			echo "<tr  class='sum1'><td width=5%>" . $value . " </td>\n";
			echo "<td width=30%> " . $key ."</td>\n";
			echo "<td width=5%><img src='images/checkbox.jpg'></td><td></td></tr>\n";
		}
		foreach ($add_on_items_array as $key => $value){			
			echo "<tr  class='sum1'><td width=5%>" . $value . " </td>\n";
			echo "<td width=30%> " . $key ."</td>\n";
			echo "<td width=5%><img src='images/checkbox.jpg'></td><td></td></tr>\n";
		}
		echo "</table>\n";
	}
	
	// ----------------------------------------------------------------------------------------------------
	// function - print Shirt totals
	// ----------------------------------------------------------------------------------------------------
	function print_shirt_totals()
	{	
		global $shirt_size_array;
		echo "<br><br><br><u>SweatShirt Totals by Size:<br></u>";
		foreach($shirt_size_array as $key => $value){
			echo $key  . " = " .$value . "<br>";
		}	
	}
	
?>