<?php 

if (isset($_POST['action'])){

	//$children = explode(';', $_POST['children']);
	//$periods = explode(';', $_POST['periods']);
	
	//foreach ($children as $child_id){
		//echo $child_id . '<br />';
		//$Child = new Child($child_id);
		//print_r($Child);
		//echo '<br><br>';
	//}
	
	//echo '<br />';
	
	//foreach ($periods as $period){
	//	echo $period . '<br />';
	//}
	
	//echo '<br />';
	
}

$admin_auth = array('user');

$d = unixtojd();
$day = date("N");
$start = $d;

switch ($day) {
	case 1:
		$start -= $day;
		break;
	case 2:
		$start += 5;
		break;
	case 3:
		$start += 4;
		break;
	case 4:
		$start += 3;
		break;
	case 5:
		$start += 2;
		break;
	case 6:
		$start++;
		break;
	case 7:
		break;
	default:
		break;
}
$start -= 14;
//$start = 2455499;

$number_of_students = 0;
$change_school = false;
if (isset($_POST['change_school'])) {
	$change_school = $_POST['change_school'];
}

$last_week = true;
if (isset($_POST['date_list'])) {
	$last_week = false;
}

require('header.php'); 
require_once('calendar.php');
include("classes/subject.php");

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_markable_children();

$selected_dates = "";

// ***** REPORT DATES ***** //
include("classes/report.php");
$reports = array();
$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date >= " . $start . " ORDER BY start_date";	
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$report = new report($row);
	//hide pesach and shavuos
	if ($report->start_date == 2455669 || $report->start_date == 2455718) continue;
	array_push($reports, $report);
	if ($selected_dates == "") {
		$selected_dates = $row['start_date'] . ":" . $row['end_date'];				
	}
}
// ***** REPORT DATES ***** //
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<title>Marking Missions - Tzivos Hashem Management System</title>
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.1.min.js"></script>
	</head>

	<body>
		
		<!-- ***** body left marking_missions ***** -->
		<div class="body left marking_missions">
						
			<H1>Print Missions</H1>
				
			<!-- ***** infobox2 marking_list clearfix ***** -->
			<div class="infobox2 marking_list clearfix">
				
				<!-- ***** CHILDREN ***** -->				
				<SELECT name="child_id" id="child_id" multiple="multiple">
					<OPTION value="-1">Please select children</OPTION>		
					<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
					<OPTION value="<?=$admin->children[$cno]->user_id;?>">
						<?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?>
					</OPTION>
					<? endfor; ?>
				</SELECT>
				<!-- ***** CHILDREN ***** -->
				
				<!-- ***** WEEKS ***** -->
				<select name="date_list" id="date_list" multiple="multiple">
				<? for ($rno = 0; $rno < count($reports); $rno++) : $report = $reports[$rno]; ?>
					<? if ( (count($reports) > 1 && $rno == 1 && $start_date == 0) || ($start_date == $report->start_date && $end_date == $report->end_date) ) :  ?>
						<? $report_name = $report->report_name; ?>
						<option selected value="<?=$report->start_date;?>:<?=$report->end_date;?>"><?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?></option>
					<? else : ?>
						<option value="<?=$report->start_date;?>:<?=$report->end_date;?>"><?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?></option>								
					<? endif; ?>
				<? endfor; ?>
				</select>				
				<!-- ***** WEEKS ***** -->
					
				<input type="button" id="childrenbutton" value="GO" />
					
			</div>
			<!-- ***** infobox2 marking_list clearfix ***** -->	
			
		</div>
		<!-- ***** body left marking_missions ***** -->
		
		<div id="info">
		</div>
		
		<form name="parents_date_tasks_report" id="parents_date_tasks_report" action="parents_print_report_george.php" method="post" accept-charset="UTF-8">
			<input type="hidden" name="action" id="action" value="produce_report">
			<input type="hidden" name="children" id="children" />
			<input type="hidden" name="periods" id="periods" />
		</form>
		
		<script type="text/javascript">
			$('#childrenbutton').click(function(){
				
				var children = '';
				$('#child_id :selected').each(function(index, selecteditem){ 
					children = children + $(selecteditem).val() + ';';
				});
				children = children.substr(0, children.length - 1);
				$('#children').val(children);
				
				var periods = '';
				$('#date_list :selected').each(function(index, selecteditem){ 
					periods = periods + $(selecteditem).val() + ';';
				});
				periods = periods.substr(0, periods.length - 1);
				$('#periods').val(periods);
				
				$('#parents_date_tasks_report').submit();
				
			});
		</script>			
		
	</body>	
	
</html>
