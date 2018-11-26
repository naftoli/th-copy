<? 
//header("Location: under_construction.php");
$admin_auth = array('user');

$d = unixtojd();
$day = date("N");
$start = $d;

switch ($day) {
    case 1:
        $end += 4;
        break;
    case 2:
        $end += 3;
        break;
    case 3:
        $end += 2;
        break;
    case 4:
        $end += 1;
        break;
    case 5:
        break;
    case 6:
        $end--;
        break;
    case 7:
		$end-=2;
        break;
    default:
        break;
}
//$start -= 14;
$start = 2455718;

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
$admin = new \camps\classes\admin($row);
$admin->get_children();

$child_id = 0;
$class_id = 0;
$user_id = 0;
$subject_id = 0;
$date_list = "";
$start_date = 0; 
$end_date = 1; 

$days_of_the_week = array("M", "T", "W", "T", "F", "S", "S");

//$today = gregoriantojd(date("n"), date("j"), date("Y"));
$today = unixtojd();	
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
	$sunday = $today - $day_of_the_week;
else
	$sunday = $today;
$report_start_date = $sunday + 7;

if (isset($_POST['child_id'])) {
	$child_id = $_POST['child_id'];
	$sql = "SELECT school_id FROM users WHERE user_id=" . $child_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$school_id = $row['school_id'];
	$subject_id = -1;
}
elseif (count($admin->children) == 1) {
	$child_id = $admin->children[0]->user_id;
	$school_id = $admin->children[0]->school_id;
}	

$selected_dates = "";
$action = "";
if (isset($_POST['action']) || count($admin->children) == 1) {

	if (isset($_POST['action']))
		$action = $_POST['action'];
	
	if ($action == "produce_report" || count($admin->children) == 1) {
	
		if (isset($_POST['child_id'])) 
			$user_id = $_POST['child_id'];		
		else
			$user_id = $admin->children[0]->user_id;	
		
		if (isset($_POST['subject_id']))		
			$subject_id = $_POST['subject_id'];
		
		include("camps/includes/classes/user_track.php");
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
			$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date = $start ORDER BY start_date";	
			//echo '<input type="hidden" name="SQL" value="' . $sql . '">';
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
			//echo '<input type="hidden" name="DATES" value="' . $start_date . ':' . $end_date .  '">';
		}
		
		$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$user = new user($row);
		$user->get_school();
		$school_id = $user->school->school_id;
		$user->get_school_class();		
		$user->get_rank();
		$user->get_user_tracks($subject_id, $start_date, $end_date);
	}
}

if ($child_id > 0 || count($admin->children) == 1) {
	// ***** SUBJECTS ***** //
	$subjects_select = array();
	$sql = "SELECT DISTINCT s.* FROM schools JOIN subjects AS s USING (inst_id) JOIN school_subjects USING (school_id, subject_id) WHERE school_id=" . $school_id . " ORDER BY s.subject_ord, s.subject_name"; 
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$subject = new subject($row);
		array_push($subjects_select, $subject);
	}
	// ***** SUBJECTS ***** //	

	// ***** REPORT DATES ***** //
	include("classes/report.php");
	$reports = array();
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date = $start";	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$report = new report($row);
		array_push($reports, $report);
		if ($selected_dates == "") {
			$selected_dates = $row['start_date'] . ":" . $row['end_date'];				
		}
	}
	// ***** REPORT DATES ***** //
}

//echo "# OF REPORTS:" . count($reports) . "<br />";
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Marking Missions - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		

	</head>

	<body>
		
		<? include('admin_header.php'); ?>
		
		<input type="hidden" name="start_date" value="<?=$start_date_greg;?>">
		<input type="hidden" name="end_date" value="<?=$end_date_greg;?>">
		
		<script type="text/javascript" src="scripts/functions.js"></script>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		<script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
		
		<script type="text/javascript">
			var user_id = <?=$user_id;?>;
			var check_all = false;
			
			//var school_id = <?=$school_id;?>;
			//var start_date = <?=$start_date;?>;
			//var end_date = <?=$end_date;?>;
			//var check_all = false;
			//var check = "";
			//var date_task_ids = "";
			//var mark_dates = "";
			//var checked = false;
			//var user_id = <?=$user_id;?>;
			//var today = <?=$today;?>;
			//var save = false;
			//var number_of_students = <?=$number_of_students;?>;
			
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
/*				
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
							}
						});					
					}
				})
				// ***** UNCHECK ALL ***** //
*/								
				$(".marking_list #display_submit").hide();

				$('.marking.module').addClass('dontsplit');
				//$('#tasks_page_two').columnize({ columns: 2 });


				var content_height = 915;
				var page_no = 2;

				multipleNewsletter = function() {
					page_no = 2;
					buildNewsletter(index_no);

					index_no++;
					if ($('.print_individual').length > index_no) {
						setTimeout('multipleNewsletter()', 0);
						percent = Math.round((index_no/$('.print_individual').length)*100,2);
						$('.generate.loading h3').text(percent + "% Complete");
					} else {
						donePrepare();
					}
				}

				buildNewsletter = function(index){
					var element = $('.print_individual:eq(' + index + ')');
					if($(element).find('.tasks_page_two').contents().length > 0){
						$page = $(element).find(".page_template").clone().addClass("page").removeClass("page_template").css("display", "block");
					
						$page.find(".print_header .page_no").text(page_no);
						$(element).append($page);
						page_no++;
					/*
						$(element).find('.tasks_page_two').columnize({
							columns: 2,
							target: ".print_individual:eq(" + index + ") .page:last .print_content",
							lastNeverTallest: true,
							overflow: {
								height: content_height,
								id: ".print_individual:eq(" + index + ") .tasks_page_two",
								doneFunc: function(){
									//console.log("done with page");
									//buildNewsletter(index);
								}
							}
						});
					*/
					}
				}

				$('.print-only').css('opacity',0);
				$('.generate:last').hide().before('<div class="module clearfix generate loading"><div class="loader"></div><h3>Processing...</h3></div>');

				donePrepare = function() {
					$('.print-only').addClass('print_only');
					$('.print-only').css('opacity',1);
					$('.generate.loading').hide();
					$('.generate:last').show();
					correctHeight();
				}

			});


		$(window).bind('load', function() {
			multipleNewsletter();
		});

		var multipleNewsletter;
		var buildNewsletter;
		var donePrepare;
		var index_no = 0;
		var percent = 0;



/*			
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
						}
					});				
				}				
			}						
*/		</script>
		
		<div class="body left marking_missions">
						
			<H1>Print Missions</H1>

<? if ($child_id < 1 && count($admin->children) > 1) : ?>

			<form name="parents_date_tasks_report" id="parents_date_tasks_report" action="parents_print_shavuos_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="produce_report">
				
				<div class="infobox2 marking_list clearfix">
				
					<!-- ***** CHILDREN ***** -->				
					<div class="child_list select_box">
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous School')?></span>
						</a>
					
						<SELECT name="child_id" id="child_id">
							<OPTION value="-1">Please select a child</OPTION>		
							<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
							<OPTION value="<?=$admin->children[$cno]->user_id;?>"><?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?></OPTION>
							<? endfor; ?>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"><?=T_('Next School')?></span>
						</a>						
					</div>
					<!-- ***** CHILDREN ***** -->
				
				
				</div>
				
			</form>
<? else : ?>			
			<form name="parents_date_tasks_report" id="parents_date_tasks_report" action="parents_print_pesach_report.php" method="post" accept-charset="UTF-8">
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
					
					
					<!-- ***** SUBJECT ***** -->
					<div class="campaign_list select_box">						
						<a class="prev button">
							<span class="icon"></span><span class="label"><?=T_('Previous Campaign')?></span>
						</a>
						
						<select name="subject_id" id="subject_id">
							<option value="-1"><?=T_('All Campaigns')?>
						</select>
						
						<a class="next button">
							<span class="icon"></span><span class="label"><?=T_('Next Campaign')?></span>
						</a>					
					</div>
					<!-- ***** SUBJECT ***** -->
					
						<!-- ***** WEEKLY PERIOD ***** -->
						<div class="date_list select_box">					
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous Week')?></span>
							</a>
							<select name="date_list" class="sSelect">
								<? for ($rno = 0; $rno < count($reports); $rno++) : ?>
									<? $report = $reports[$rno]; ?>
									<? $report_name = $report->report_name; ?>
									<option selected value="<?=$report->start_date;?>:<?=$report->end_date;?>"><?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?></option>
								<? endfor; ?>
							</select>
							
							<a class="next button">
								<span class="icon"></span><span class="label"><?=T_('Next Week')?></span>
							</a>
						</div>
						<!-- ***** WEEKLY PERIOD ***** -->
									
				</div>				

			</form>
				<div class="noprint">
					<div class="module clearfix">
						<div class="list_expand">
							<ul>
								<li>
									<h3><span class="icon"></span>Print Instructions</h3>
									<p><img src="images/Print-Dialog-Small-2.jpg" align="right" /><img src="images/Print-Dialog-Small-1.jpg" align="right" />
										In your browser click 'File' then 'Page Setup...'</p>
										<p>Step 1: Set the Orientation to Portrait</p>
										<p>Step 2: Check 'Shrink to fit Page Width'</p>
										<p>Step 3: In Options check 'Print Background (colors & images)'</p>
										<p>Step 4: In the second tab set all Margins to 0.5 inches (All Sides)</p>
										<p>Step 5: Set all Headers & Footers to Blank</p>
										<p>Note: The browser will save these preferences for later use.</p>
								</li>
							</ul>
						</div>
					</div>
					<div class="module clearfix generate">
						<p><a href="javascript:window.print()" class="button">Print</a></p>
					</div>
				</div>

<DIV id="tasks_div">
	
	<? if ($action == "produce_report" || count($admin->children) == 1) : ?>			
	<div class="print_individual">						
	<div class="print_header">
		<div class="marking module clearfix dontsplit">
													
			<div class="rank_image"><img height="70" src="/file_view.php?id=<?=$user->rank_image_id;?>"></div>
			<div class="user_image"><img height="70" src="/file_view.php?id=<?=$user->user_photo_id;?>"></div>
			<p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
			<p class="print_week">My Missions for the week of <?=$report_name;?></p>
			<p class="print_class">Grade: <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_sub;?> <?=$user->school_class->class_teacher;?></p>
			<p class="print_sig">Parent's Signature<span></span></p>
		</div>
	</div>
	
	
	
	<? include("daily_tasks.php"); ?>

	<div class="page_template" style="display:none;">
		<div style="clear:both; height:1px;"></div>
		<div class="print_header print_page_two">
			<div class="marking module clearfix">
				<p class="print_week"><?=$report_name;?></p>
				<p class="print_page">Page <span class="page_no">2</span> - &#1489;"&#1492;</p>
				<p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
			</div>
		</div>
		<div class="print_content"></div>
		<div class="footer_sticker print_only">
	<?
	$school_type = $user->school_type_id;
	if ($school_type == 2 || $school_type == 3) 
		echo "<img src='images/stickers/All.gif' height=60 />";
	else 
		echo "<img src='images/stickers/AllDaySchool.gif' height=60 />";
	?>		
	</div>

	</div>

	<div class="tasks_page_two">	
		<? include("weekly_tasks.php"); ?>
		<? include("no_label_tasks.php"); ?>
		<? include("shabbos_tasks.php"); ?>
	</div>
	</div>


</DIV>
	
	<? endif; ?> <!-- if ($action == "produce_report") -->
			
<? endif; ?> <!-- if ($school_id == 0) -->
			
		</div> <!-- <div class="body"> -->
		
		
	</body>	
</html>
