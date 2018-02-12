<? 
//header("Location: under_construction2.php");
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<script src="scripts/jquery.min.js"></script>
	<script src="scripts/jquery.styleselect.js"></script>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Charge Card Report</title>
<style type='text/css'>
tr, th, td {
	border: 1px dashed black;
	padding: 10px;
	font-size: 12px;
}
</style>
<script>
	$(function() {
		$('select').sSelect();
	});
</script>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin_auth[0] == 'school') : ?>
<? //print_r($admin); ?>

<h1>Charge Card Report</h1>
<?
if (isset($_POST['submit'])) {

	//print_r($_POST); exit; 
	$from = $_POST['from'];
	$to = $_POST['to'];
    //$weeks = (($to - $from) + 1) / 7;
	//$school_id = isset($_POST['school']) ? $_POST['school'] : $admin->school_id;
    require_once 'class.adminSchools.php';      
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
    
    echo "<div align='center'>";
    echo "<input type='button' value='Print' onclick='window.print()'>";
    echo "<br />";
    echo "</div>";
    
    foreach ( $schools as $school_id => $name ) {       
        echo "<h2>" . $name . "</h2>";	
    	$sql = "
    		select count(u.user_id) as total, 
    		s.school_name, u.last, 
    		u.first, c.class_grade, 
    		c.class_sub, dt.daily_task 
    		from date_tasks_marks as dtm 
    		join date_tasks as dt using (date_task_id) 
    		join users as u using (user_id) 
    		join classes as c using (class_id) 
    		join schools as s on (u.school_id = s.school_id) 
    		where dt.focus_task = 1 
    		and dtm.mark_date <= $to 
    		and dtm.mark_date >= $from 
    		and s.school_id = $school_id 
    		group by u.user_id 
    		order by c.class_grade, c.class_sub, 
    		u.last, u.first
    		";
        //echo $sql;            
    	$result = mysql_query($sql);

        echo "<div align='center'>";
    	echo "<table>";
    	echo "<tr align='center'><th>Grade</th><th>Name</th><th>Number of cards</th>";
        $total = 0;
    	while ($row = mysql_fetch_assoc($result)) {
    		if ($row['daily_task']) {
    	    	$numCards = (int)($row['total'] / 5);
			} else {
				$numCards = (int)($row['total']);
			} 
    		$grade = $row['class_sub'] == '' ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
    		echo "<tr><td>" . $grade . "</td><td>" . $row['last'] . ", " . $row['first'] . 
    		    "</td><td align='center'>" . $numCards . "</td></tr>";
    		$total += $numCards;
    	}
    	echo "<tr><td colspan='2' align='right'>Total:</td><td align='center'>" . $total . "</td></tr>";
    	echo "</table>";
    	echo "</div>";
    }
} else {
	// get current working year
	require_once 'class.globalSettings.php';
	$startEnd = GlobalSettings::getCurYearDates();
	
	$dates = array();
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' and start_date >= " . $startEnd['start'] . " ORDER BY start_date";	
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$dates[] = $row;
	}
	?>
	<p>Please choose the dates that you would like to use, in order to generate the report.<br />
	<form method='post' action='charge_card_report.php'>
	<?
	if ($admin->auth == 'super') {
		$sql = "select * from schools where school_era is null order by school_name";
		$result = mysql_query($sql);
		echo "Choose school:<br /><select name='school'>";
		while ($row = mysql_fetch_assoc($result)) {
			echo "<option value='" . $row['school_id'] . "'>" . $row['school_name'];
		}
		echo "</select><br /><br /><br />";
	}
	?>
	From beginning of:<br /><select name='from'>
	<?
	$now = unixtojd();
	foreach ($dates as $date) {
		if ($now >= ($date['start_date']+7) && $now <= ($date['end_date']+7)) 
			echo "<option value='" . $date['start_date'] . "' selected='selected'>" . $date['report_name'] . "</option>";
		else 
			echo "<option value='" . $date['start_date'] . "'>" . $date['report_name'] . "</option>";
	}
	?>
	</select><br /><br /><br />
	Until end of:<br /><select name='to'>
	<?
	foreach ($dates as $date) {
		if ($now >= ($date['start_date']+7) && $now <= ($date['end_date']+7)) 
			echo "<option value='" . $date['end_date'] . "' selected='selected'>" . $date['report_name'] . "</option>";
		else 
			echo "<option value='" . $date['end_date'] . "'>" . $date['report_name'] . "</option>";
	}
	?>
	</select><br /><br /><br />
	<input type='submit' name='submit' value='submit'>
	</form>
<?
}
?>

<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>