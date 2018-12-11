<? 
//header("Location: under_construction.php");
$admin_auth = array('user');

$d = unixtojd();
$day = date("N");
$end = $d;
//echo $d;

switch ($day) {
    case 1:
        $end += 3;
        break;
    case 2:
        $end += 2;
        break;
    case 3:
        $end += 1;
        break;
    case 4:
        break;
    case 5:
		$end += 6;
        break;
    case 6:
        $end += 5;
        break;
    case 7:
		$end += 4;
        break; 
    default:
        break;
}
//echo $end;
$start = ($end - 6);
$report_start_date = $start;
$four_weeks_ago = ($start - 28);
//$four_weeks_ago = ($start - 105);
//$start = 2457179;

require('header.php'); 
require_once('calendar.php');
include("classes/subject.php");

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
//$admin->get_children();
$admin->get_markable_children();

$subject_id = -1;
$date_list = ""; 
$days_of_the_week = array("M", "T", "W", "T", "F", "S", "S");

if (isset($_POST['child_id'])) {
	$child_id = $_POST['child_id'];
} else {
	$child_id = $admin->children[0]->user_id;
}	

$user_id = $child_id;
$selected_dates = "";
$action = "";	
		
include("classes/user_track.php");
include 'class.taskExceptions.php';
include("classes/date_tasks_mission.php");
include("classes/daily_task.php");
include("classes/weekly_task.php");
include("classes/shabbos_task.php");
include("classes/no_label_task.php");
include("classes/task.php");
include("classes/date_tasks_mark.php");

if (isset($_POST['date_list'])) {
	$date_list = explode(":", $_POST['date_list']);
	$start_date = $date_list[0]; 
	$end_date = $date_list[1];
	$selected_dates = $start_date . ":" . $end_date;
}
else {
	$start_date = $start;
	$end_date = $end;
}

if ($start_date >= 2456906) {
	$days_of_the_week = array("ש", "S", "M", "T", "W", "T", "F");
}

echo "<input type='hidden' name='dates' value='start: " . $start_date . "; end: " . $end_date . "'>";

$sql = "SELECT * FROM users WHERE user_id=" . $user_id; 
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);
$user->get_school();
$school_id = $user->school->school_id;
$user->get_school_class();		
$user->get_rank();
$user->get_user_tracks($subject_id, $start_date, $end_date);

// ***** REPORT DATES ***** //
include("classes/report.php");
$reports = array();
$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date >= $four_weeks_ago AND end_date <= " . $end . " ORDER BY start_date";	
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$report = new report($row);
	array_push($reports, $report);
	if ($report->start_date == $start_date) {
		$report_name = $report->report_name;
	}
}
//echo $report_name; exit;
// ***** REPORT DATES ***** //
//echo "# OF REPORTS:" . count($reports) . "<br />";
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Marking Missions - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script language="javascript" type="text/javascript">
			var no_of_markable_children = <?=count($admin->children);?>
			
			function check_no_of_children()
			{
				if (no_of_markable_children == 0)
					alert("You have no access to this page. Please contact your school administrators.");
			}
		</script>
		
		<style type="text/css">
            .checkall {
                margin-right: -5px;
                float: right;
            }
        </style>
	</head>

	<body onload="check_no_of_children()">
		
		<? include('admin_header.php'); ?>
		
		<script type="text/javascript" src="scripts/functions.js"></script>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		<script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
		
		<script type="text/javascript">
			var user_id = <?=$user_id;?>;
			var check_all = false;
			
			$(function(){
				$('.marking_list div select').each(function() {
					if (!$(this).find('option:selected').next().val()) $(this).siblings('a.next').addClass('disabled');
					if (!$(this).find('option:selected').prev().val()) $(this).siblings('a.prev').addClass('disabled');
				});
				
				$('.marking_list div a.next').click(function(){
					$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
				});
				
				$('.marking_list div a.prev').click(function(){
					$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
				});
				
				// ***** Child List Select ***** //
				$(".child_list select").sSelect().change(function () {
					var child_id = $(this).val();
					
					if (child_id > 0)
						document.forms["parents_date_tasks_report"].submit();
				})
				// ***** Child List Select ***** //
								
				$(".campaign_list select").sSelect().change(function () {
					if (number_of_students > 0)
						$(this).closest('form').submit();
				})
				
				$(".date_list select").sSelect().change(function () {
						document.forms["parents_date_tasks_report"].submit();
				})
				
				$('.slider:last .list_expand li h3').nextAll().hide();
				$('.slider:last .list_expand li h3').click(function(){
					$(this).nextAll().slideToggle('fast');
					$(this).parents('li').toggleClass('open');
				});
				
				// ***** CHECK ALL ***** //
				$('.marking_list .check_all a').click(function() {
					date_task_ids = "";
					mark_dates = "";
					
					check_all = true;
					var tasks_div = document.getElementById("tasks_div");
					var unchecked_checkboxes = $(tasks_div).find('span.checkbox_span.unchecked');
					$(unchecked_checkboxes).trigger("click");					
					check_all = false;
					
					if (date_task_ids.length > 0) {
						date_task_ids = date_task_ids.substr(0, date_task_ids.length - 1);
						mark_dates = mark_dates.substr(0, mark_dates.length - 1);
						
						var function_name = "add_date_tasks_marks";	
						var parameters = [user_id, date_task_ids, mark_dates];
						var url = "add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					
						$.getJSON(url, function(success) {	
							if (success == false) {
								alert("1:Update not performed.");
							} else {
								$.post('ajax/updateMedalsRanks.php', { user : user_id });
							}
						});	
					}
				})
				// ***** CHECK ALL ***** //
				
				// ***** UNCHECK ALL ***** //
				$('.marking_list .uncheck_all a').click(function() {
					date_task_ids = "";
					mark_dates = "";
														
					check_all = true;					
					var tasks_div = document.getElementById("tasks_div");
					$(tasks_div).find('span.checkbox_span.checked').trigger("click");
					check_all = false;
					
					if (date_task_ids.length > 0) {
						date_task_ids = date_task_ids.substr(0, date_task_ids.length - 1);
						mark_dates = mark_dates.substr(0, mark_dates.length - 1);
						
						var function_name = "delete_date_tasks_marks";					
						var parameters = [user_id, date_task_ids, mark_dates];
						var url = "delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
						$.getJSON(url, function(success) {	
							if (success == false) {
								alert("2:Update not performed.");
							} else {
								$.post('ajax/updateMedalsRanks.php', { user : user_id });
							}
						});					
					}
				})
				// ***** UNCHECK ALL ***** //
				
				//check all in row
                $(".checkall").click( function() { 
                    //if check is true then box was checked, otherwise it was unchecked
                    var check = $(this).is(":checked");
                    if ( check ) {
                        var unchecked = $(this).closest(".row").find("span.checkbox_span.unchecked");
                        $(unchecked).trigger("click"); //check all unchecked boxes in row
                    } else {
                        var checked = $(this).closest(".row").find("span.checkbox_span.checked");
                        $(checked).trigger("click"); //uncheck all checked boxes in row
                    }
                });
								
				$(".marking_list #display_submit").hide();

				$('.marking.module').addClass('dontsplit');
				$('#tasks_page_two').columnize({ columns: 2 });

			});
			
			function update_weekly_shabbos_date_task(span, date_task_id, mark_date) {
				if ($(span).hasClass('checked')) {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "delete_task_mark";
					var url = "delete_functions.php";
					
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox checked");						
					$(parent_div).addClass("cell checkbox");
					$(parent_div).addClass("unchecked");
					$(span).removeClass('checked'); 
					$(span).addClass("unchecked");						
				}
				else {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "add_task_mark";
					var url = "add_functions.php";
				
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox unchecked");						
					$(parent_div).addClass("cell checkbox checked");						
					$(span).removeClass('unchecked'); 
					$(span).addClass("checked");						
				}
				
				if (check_all == false) {
					var parameters = [user_id, date_task_id, mark_date];
					url = url + "?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {	
						if (success == false) {
							alert("3:Update not performed.");
						} else {
							$.post('ajax/updateMedalsRanks.php', { user : user_id });
						}
					});					
				}				
			}			
			
			function update_daily_date_task(span, date_task_id, mark_date) {
				if ($(span).hasClass('checked')) {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "delete_daily_task_mark";
					var url = "delete_functions.php";
					
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox checked");						
					$(parent_div).addClass("cell checkbox");
					$(parent_div).addClass("unchecked");
					$(span).removeClass('checked'); 
					$(span).addClass("unchecked");						
				}
				else {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "add_daily_task_mark";
					var url = "add_functions.php";
					
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox unchecked");						
					$(parent_div).addClass("cell checkbox checked");						
					$(span).removeClass('unchecked'); 
					$(span).addClass("checked");						
				}
				
				if (check_all == false) {
					var parameters = [user_id, date_task_id, mark_date];
					url = url + "?function_name=" + function_name + "&parameters=" + parameters;
				
					$.getJSON(url, function(success) {	
						if (success == false) {
							alert("4:Update not performed.");
						} else {
							$.post('ajax/updateMedalsRanks.php', { user : user_id });
						}
					});					
				}
			}
			
			function submit_form() {
				var form = document.getElementById("update_date_tasks_report");
				
				// ***** DAILY ***** //
				var daily_tasks = "";
				var daily_div = document.getElementById("daily_div");
				var daily_checked_checkboxes = $(daily_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < daily_checked_checkboxes.length; cbno++) {
					var checkbox = daily_checked_checkboxes[cbno];
					daily_tasks = daily_tasks + checkbox.id + ":true;";
				}
				var daily_unchecked_checkboxes = $(daily_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < daily_unchecked_checkboxes.length; cbno++) {
					var checkbox = daily_unchecked_checkboxes[cbno];
					daily_tasks = daily_tasks + checkbox.id + ":false;";
				}
				form.elements["daily_tasks"].value = daily_tasks;
				// ***** DAILY ***** //
				
				// ***** WEEKLY ***** //
				var weekly_tasks = "";
				var weekly_div = document.getElementById("weekly_div");
				var weekly_checked_checkboxes = $(weekly_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < weekly_checked_checkboxes.length; cbno++) {
					var checkbox = weekly_checked_checkboxes[cbno];
					weekly_tasks = weekly_tasks + checkbox.id + ":true;";
				}				
				var weekly_unchecked_checkboxes = $(weekly_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < weekly_unchecked_checkboxes.length; cbno++) {
					var checkbox = weekly_unchecked_checkboxes[cbno];
					weekly_tasks = weekly_tasks + checkbox.id + ":false;";
				}	
				form.elements["weekly_tasks"].value = weekly_tasks;
				// ***** WEEKLY ***** //
				
				// ***** SHABBOS ***** //
				var shabbos_tasks = "";
				var shabbos_div = document.getElementById("shabbos_div");
				var shabbos_checked_checkboxes = $(shabbos_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < shabbos_checked_checkboxes.length; cbno++) {
					var checkbox = shabbos_checked_checkboxes[cbno];
					shabbos_tasks = shabbos_tasks + checkbox.id + ":true;";
				}								
				var shabbos_unchecked_checkboxes = $(shabbos_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < shabbos_unchecked_checkboxes.length; cbno++) {
					var checkbox = shabbos_unchecked_checkboxes[cbno];
					shabbos_tasks = shabbos_tasks + checkbox.id + ":false;";
				}	
				form.elements["shabbos_tasks"].value = shabbos_tasks;
				// ***** SHABBOS ***** //
				
				// ***** NO LABELS ***** //
				var no_labels_tasks = "";
				var no_labels_div = document.getElementById("no_labels_div");
				var no_labels_checked_checkboxes = $(no_labels_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < no_labels_checked_checkboxes.length; cbno++) {
					var checkbox = no_labels_checked_checkboxes[cbno];
					no_labels_tasks = no_labels_tasks + checkbox.id + ":true;";
				}												
				var no_labels_unchecked_checkboxes = $(no_labels_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < no_labels_unchecked_checkboxes.length; cbno++) {
					var checkbox = no_labels_unchecked_checkboxes[cbno];
					no_labels_tasks = no_labels_tasks + checkbox.id + ":false;";
				}
				form.elements["no_labels_tasks"].value = no_labels_tasks;
				// ***** NO LABELS ***** //				
			}
			
			function check_number_of_students(number_of_students) {
				if (number_of_students == 0) {
					alert("There are no students currently registered in this school. Please choose another school.");
					return false;
				}
				else {
					return true;
				}
			}
			
			function update_mark(txtbx, date_task_id, mark_date) {
				var user_mark = txtbx.value;
				
				if (user_mark > 0) {
					var function_name = "add_mark";
					var parameters = [user_id, date_task_id, mark_date, user_mark];
					var url = "add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
					$.getJSON(url, function(success) {
						if (success != 1) {
							alert("5:Update not performed. Please try again.");
						} else {
							$.post('ajax/updateMedalsRanks.php', { user : user_id });
							/*
                        	$.post('ajax/updateBpByTaskID.php', {
								task : date_task_id, 
								user : user_id 
							});
							*/
                        }
					});					
				}
				else {
					var function_name = "delete_mark";
					var parameters = [user_id, date_task_id, mark_date];
					var url = "delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
					$.getJSON(url, function(success) {
						if (success != 1) {
							//alert("6:Update not performed. Please try again.");
						} else {
							$.post('ajax/updateMedalsRanks.php', { user : user_id });
						}
					});				
				}				
			}						
		</script>
		
		<div class="body left marking_missions">
						
			<input type="hidden" name="# of children" value="<?=count($admin->children);?>">
			
			<H1>Marking Missions</H1>
	
		<form name="parents_date_tasks_report" id="parents_date_tasks_report" action="parents_date_tasks_report_new.php" method="post" accept-charset="UTF-8">
			<input type="hidden" name="action" id="action" value="produce_report">
			
			<div class="infobox2 marking_list clearfix noprint">
							
					<? if (count($admin->children) > 1) : ?>
					<!-- ***** CHILDREN ***** -->			
					<div class="child_list select_box">
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous School')?></span>
						</a>
					
						<SELECT name="child_id" id="child_id">
							<OPTION value="-1">Please select a child</OPTION>		
							<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
								<? if ($child_id == $admin->children[$cno]->user_id) : ?>
								<OPTION selected value="<?=$admin->children[$cno]->user_id;?>"><?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?></OPTION>
								<? else : ?>
								<OPTION value="<?=$admin->children[$cno]->user_id;?>"><?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?></OPTION>
								<? endif; ?>
							<? endfor; ?>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"><?=T_('Next School')?></span>
						</a>						
					</div>
					<!-- ***** CHILDREN ***** -->
					<? else : ?>
					<input type="hidden" name="child_id" id="child_id" value="<?=$admin->children[0]->user_id;?>">
					<? endif; ?>
				
					<!-- ***** WEEKLY PERIOD ***** -->
					<div class="date_list select_box">					
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous Week')?></span>
						</a>
						
						<select name="date_list" class="sSelect">
							<? for ($rno = 0; $rno < count($reports); $rno++) : ?>
								<? $report = $reports[$rno]; ?>									
								<? if ($report->start_date == $start_date) : ?>
								<? $report_name = $report->report_name; ?>
								<option selected value="<?=$report->start_date;?>:<?=$report->end_date;?>"><?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?></option>
								<? else : ?>
								<option value="<?=$report->start_date;?>:<?=$report->end_date;?>"><?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?></option>								
								<? endif; ?>
							<? endfor; ?>
						</select>
						
						<a class="next button">
							<span class="icon"></span><span class="label"><?=T_('Next Week')?></span>
						</a>
					</div>
					<!-- ***** WEEKLY PERIOD ***** -->
								
					<div class="select_box check_all clearfix">
						<a class="button">
							<span class="icon"></span><?=T_('Check All')?>
						</a>
					</div>
						
					<div class="select_box uncheck_all clearfix">
						<a class="button">
							<span class="icon"></span><?=T_('Uncheck All')?>
						</a>						
					</div>
				
			</div>				

		</form>
		
<DIV id="tasks_div">
						
	<div class="print_header">
		<div class="marking module clearfix dontsplit">
													
			<div class="rank_image"><img height="70" src="/file_view.php?id=<?=$user->rank_image_id;?>"></div>
			<div class="user_image"><img height="70" src="/file_view.php?id=<?=$user->user_photo_id;?>"></div>
			<p class="print_name"><?=$user->rank_name;?> 
			    <?= empty($user->first_he) ? $user->first : $user->first_he;?> 
                <?= empty($user->last_he) ? $user->last : $user->last_he;?></p>
			<p class="print_week">
            	My Missions for the week of <?=$report_name;?>
            	<br /><span style='font-size: 10px'>(
        		<?
        		$hDate['start'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($start_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
        		$hDate['end'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($end_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
        		echo $hDate['start'] . ' - ' . $hDate['end'];
        		?>
        		)</span>
            </p>
			<p class="print_class">Grade: <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_sub;?> <?=$user->school_class->class_teacher;?></p>
			<p class="print_sig">Parent's Signature<span></span></p>
		</div>
	</div>
	
	<div style="clear:both; height:1px;"></div>
	
	<? include("daily_tasks_new.php"); ?>
		
	<div class="print_header print_page_two">
		<div class="marking module clearfix">
			<p class="print_week"><?=$report_name;?></p>
			<p class="print_page">Page 2 - &#1489;"&#1492;</p>
			<p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
		</div>
	</div>
	<div id="tasks_page_two">	
		<? include("weekly_tasks.php"); ?>
		<? include("no_label_tasks.php"); ?>
		<? include("shabbos_tasks.php"); ?>
	</div>
	
</DIV>
							
	</div> <!-- <div class="body"> -->
		
	</body>	
</html>
