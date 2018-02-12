<? 
$admin_auth = array('user');
require('header.php'); 

$d = unixtojd();
$day = date("N");
$end = $d;

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
		$end += 6 ;
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
$start = ($end - 34); //6 days back for start plus 4 weeks
$report_start_date = ($end - 6);
$start = 2457641;
$end = 2458005;

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
//$admin->get_children();
$admin->get_markable_children();

$children = array();
foreach ($admin->children as $child) {
	//filter out children with no school/class id
	if (!empty($child->school_id) && !empty($child->class_id)) {
		$children[] = $child;
	}
}

$days_of_the_week = array("M", "T", "W", "T", "F", "ש", "S");	

$selected_dates = "";
$action = "";
$subject_id = -1;

//include("classes/user.php");
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
	$start_date = $report_start_date;
	$end_date = $end;
}

if ($start_date >= 2456906) {
	$days_of_the_week = array("ש", "S", "M", "T", "W", "T", "F");
}

include("classes/report.php");
$reports = array();
$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date >= " . $start . " ORDER BY start_date";	
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$report = new report($row);
	//hide pesach
	if (in_array($report->start_date, array(2456761, 2456768))) continue;
	//if ($report->start_date >= 2456927) continue;
	array_push($reports, $report);
	if ($selected_dates == "") {
		$selected_dates = $row['start_date'] . ":" . $row['end_date'];				
	}
}

$users = array();
foreach ($children as $child) {
	if (isset($_POST['child_id']) && $_POST['child_id'] > 0) {
		if ($_POST['child_id'] != $child->user_id) {
			continue;
		}
	}
	$sql = "SELECT * FROM users WHERE user_id=" . $child->user_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$user = new user($row);
	$user->get_school();
	$school_id = $user->school->school_id;
	$user->get_school_class();		
	$user->get_rank();
	$user->get_user_tracks($subject_id, $start_date, $end_date);
	$users[] = $user;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Print Missions</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
		    @media print {
		        .no-print {
		            display: none;
		        }
		    }
		    .footer_sticker {
                width: 550px;
                margin: 0 auto;
            }
            .footer_info {
                height: 50px;
                width: 50px;
                font-size: 6px;
                text-align: center;
                float: left;
            }
            .footer_info img {
                height: 40px;
            }
		</style>
	</head>

	<body>
		
		<div id="info">
		</div>
		
		<? include('admin_header.php'); ?>
		
		<script type="text/javascript" src="scripts/functions.js"></script>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		<script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
		
		<script type="text/javascript">
			var start_date = <?=$start_date;?>;
			
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
								
				$(".marking_list #display_submit").hide();

				$('.marking.module').addClass('dontsplit');
				//$('#tasks_page_two').columnize({ columns: 2 });

				var content_height = 950;
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
                        
                        var user_id = $('#child_id').val();
                        var str = "";
                        $.ajax({
                            url: 'ajax/getUserMissionInfo.php', 
                            async: false, 
                            data: { user_id : user_id, type : 'All' }, 
                            success: function( data, textStatus, jqXHR ) {
                                data = $.parseJSON( data );
                                $.each( data, function( i, val ) { 
                                    str += "<span class='footer_info'>";
                                    var j = 0;
                                    $.each( val, function( indx, value ) {
                                        //build footer info
                                        if ( j++ == 0 ) { //first get sticker info
                                            str += indx + "<br /><img src='images/stickers/Sticker-" + value + ".gif'><br />";
                                        } else { //then get medal info
                                            str += value + " to " + indx;
                                        }
                                    });
                                    str += "</span>";
                                });
                            }
                         });
                        
                        $page.append("<div class='footer_sticker' id='footer_sticker'>" + str + "</div>");
						
						if (start_date != 2456019 && start_date != 2456075) { 
							
							$(element).find('.tasks_page_two').columnize({
								columns: 2,
								target: ".print_individual:eq(" + index + ") .page:last .print_content",
								lastNeverTallest: true,
								overflow: {
									height: content_height,
									id: ".print_individual:eq(" + index + ") .tasks_page_two",
									doneFunc: function(){
										buildNewsletter(index);
									}
								}
							});
						}
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
		</script>
		
		<div class="body left marking_missions">
						
			<H1>Print Missions</H1>

			<form name="parents_date_tasks_report" id="parents_date_tasks_report" action="parents_print_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="produce_report">
				
				<div class="infobox2 marking_list clearfix noprint">
									
						<!-- ***** CHILDREN ***** -->			
						<div class="child_list select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous Child')?></span>
							</a>
						
							<SELECT name="child_id" id="child_id">
								<OPTION value="0">All Children</OPTION>		
								<? for ($cno = 0; $cno < count($children); $cno++) : ?>
									<? if ($_POST['child_id'] == $children[$cno]->user_id) : ?>
									<OPTION selected value="<?=$children[$cno]->user_id;?>"><?=$children[$cno]->first;?> <?=$children[$cno]->last;?></OPTION>
									<? else : ?>
									<OPTION value="<?=$children[$cno]->user_id;?>"><?=$children[$cno]->first;?> <?=$children[$cno]->last;?></OPTION>
									<? endif; ?>
								<? endfor; ?>
							</SELECT>
							
							<a class="next button">
								<span class="icon"></span>
								<span class="label"><?=T_('Next Child')?></span>
							</a>						
						</div>
						<!-- ***** CHILDREN ***** -->					
					
						<!-- ***** WEEKLY PERIOD ***** -->
						<div class="date_list select_box">					
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous Week')?></span>
							</a>
							<select name="date_list" class="sSelect">
								<? for ($rno = 0; $rno < count($reports); $rno++) : ?>
									<? $report = $reports[$rno]; ?>
									<? if ($start_date == $report->start_date) :  ?>
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

<? foreach ($users as $user) { ?> 
	
<DIV id="tasks_div">
		
	<!-- print_individual -->
	<div class="print_individual">	
			
		<!-- print_header -->
		<div class="print_header">
			<div class="marking module clearfix dontsplit">
													
				<div class="rank_image"><img height="70" src="/file_view.php?id=<?=$user->rank_image_id;?>"></div>
			<?
			if ($user->user_photo_id) { 
			?>
				<div class="user_image"><img height="70" src="/file_view.php?id=<?=$user->user_photo_id;?>"></div>
			<? } ?>
				<p class="print_name"><?=$user->rank_name;?> 
				    <?= empty($user->first_he) ? $user->first : $user->first_he;?> 
                    <?= empty($user->last_he) ? $user->last : $user->last_he;?></p>
				<p class="print_week">
                    My Missions for Shabbos <?=$report_name;?> and following week
                	<br /><span style='font-size: 10px'>
            		<?
            		$hDate['start'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($start_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
            		$hDate['end'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($end_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
            		echo '(' . $hDate['start'] . ' - ' . $hDate['end'] . ')';
            		?>
            		</span>
                </p>
				<? if ( isset( $user->school_class->class_grade ) ) { ?>
				<p class="print_class">Grade: <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_sub;?> <?=$user->school_class->class_teacher;?></p>
				<? } ?>
				<p class="print_sig">Parent's Signature<span></span></p>
			</div>
		</div>
		<!-- print_header -->
	
		<!-- DAILY TASKS -->
		<? include("daily_tasks.php"); ?>
		<!-- DAILY TASKS -->

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
		//echo "<img src='images/stickers/All.gif' height=60 />";
	?>		
		</div>
	</div>

	<div class="tasks_page_two">	
		<? include("weekly_tasks.php"); ?>
		<? include("no_label_tasks.php"); ?>
		<? include("shabbos_tasks.php"); ?>
	</div>
	</div>
	<!-- print_individual -->

</DIV>
<? } ?>	
						
</div> <!-- <div class="body"> -->
		
	</body>	
</html>
