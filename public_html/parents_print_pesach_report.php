<? 
//header("Location: under_construction.php");
$admin_auth = array('user');

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

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
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

//set dates
$dates['start'] = array(2456761, 2456768);
$dates['end'] = array(2456767, 2456774);

//get report names
$report_names = array();
$sql = "SELECT report_name FROM reports 
    WHERE report_type='mission_cover_sheet' 
    AND visibility != 'none' 
    and start_date >= 2456761  
    and end_date <= 2456774  
    ORDER BY start_date"; 
//echo $sql;   
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
    $report_names[] = $row['report_name']; 
}

function generateMissions($row, $start_date, $end_date, $report_name) { 
    $user = new user($row);
    $user->get_school_class();
    $user->get_rank();
    $user->get_user_tracks(-1, $start_date, $end_date);
    $user->start_date = $start_date;
	$user->end_date = $end_date;             
    return array($report_name => $user);
}

$students = array();
foreach ($children as $child) {
	if (isset($_POST['child_id']) && $_POST['child_id'] > 0) {
		if ($_POST['child_id'] != $child->user_id) {
			continue;
		}
	}
	$sql = "SELECT * FROM users WHERE user_id=" . $child->user_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	for ($i = 0; $i < count($dates['start']); $i++) { 
		$students[] = generateMissions($row, $dates['start'][$i], $dates['end'][$i], $report_names[$i]);
	}
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
                var content_height_one = 930;
                var page_no = 0;

                multipleNewsletter = function() {
                    page_no = 1;
                    buildNewsletter(index_no);

                    index_no++;
                    if ($('.print_individual').length > index_no) {
                        setTimeout('multipleNewsletter()', 0);
                        percent = Math.round((index_no/$('.print_individual').length)*100,2);
                        $('.generate.loading h3').text(percent + "% Complete");
                    } else {
                        donePrepare(index_no);
                    }
                }

                buildNewsletter = function(index){
                    var element = $('.print_individual:eq(' + index + ')');
                    if($(element).find('#daily_div').contents().length > 1){

                        if (page_no == 1) {
                            $(element).find('#daily_div').after('<div class="page"><div style="clear:both;height:1;"></div><div class="print_content"></div></div>');
                        } else {
                            $page = $(element).find(".page_template").clone().addClass("page").removeClass("page_template").css("display", "block");
                            $page.find(".page_no").text(page_no);
                            $(element).find('.page:last').after($page);
                        }
                        page_no++;
                        
                        $(element).find('#daily_div').columnize({
                            columns:1,
                            target: '.print_individual:eq('+index+') .page:last .print_content',
                            overflow: {
                                height: content_height_one,
                                id: ".print_individual:eq(" + index + ") #daily_div",
                                doneFunc: function(){
                                    //console.log("done with page");
                                    buildNewsletter(index);
                                }
                            }
                        });
                        
                        //fix for pre1a showing going to sleep before waking up
                        if ( $(element).find('#daily_div').contents().length < 40 )
                            $(element).find('#daily_div').insertAfter('.print_individual:eq('+index+') .page .print_content:first');
                        
                    } else  if($(element).find('.tasks_page_two').contents().length > 0){
                        $page = $(element).find(".page_template").clone().addClass("page").removeClass("page_template").css("display", "block");
                    
                        $page.find(".print_header .page_no").text(page_no);
                        $(element).append($page);
                        page_no++;
                                                
                        var e = ".child_type:eq(" + index + ")";
                        var image = $(e).val();                        
                        
                        var c = ".user_id:eq(" + index + ")";
                        var user_id = $(c).val();
                        
                        var str = "";
                        $.ajax({
                            url: 'ajax/getUserMissionInfo.php', 
                            async: false, 
                            data: { user_id : user_id, type : image }, 
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
                        
                        $page.append("<div style='height: 30px;'></div><div class='footer_sticker' name='footer_sticker'>" + str + "</div>");
                        
                        if ( image != 'All' ) {
                            var e = document.getElementsByClassName('footer_sticker'); 
                            e[index].style.width = '400px';
                        }
                    }
                }

                $('.print-only').css('opacity',0);
                $('.generate:last').hide().before('<div class="module clearfix generate loading"><div class="loader"></div><h3>Processing...</h3></div>');

                donePrepare = function(index) {
                    $('.print-only').addClass('print_only');
                    $('.print-only').css('opacity',1);
                    $('.generate.loading').hide();
                    $('.generate:last').show();
                    correctHeight();
                }
                
                $('.tasks_page_three').columnize({
                    columns: 2,
                    lastNeverTallest: true, 
                    overflow: {
                        height: content_height, 
                        id: "tasks_page_four", 
                        doneFunc: function() {
                        }
                    }
                });
                
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

			<div class="infobox noprint">
                Pesach missions include 2 weeks worth of missions. Pesach and Parshas Kedoshim.
            </div>
            
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

			<div class="print-only">
                <? 
                foreach ( $students as $users ) { 
                    foreach ( $users as $report_name => $user ) {
                    	$start_date = $user->start_date;
						$end_date = $user->end_date;
						
						if ($start_date >= 2456906) {
							$days_of_the_week = array("ש", "S", "M", "T", "W", "T", "F");
						} else {
							$days_of_the_week = array("M", "T", "W", "T", "F", "ש", "S");
						}
                    ?>
                    <div class="print_individual">                      
                    <div class="print_header">
                        <div class="marking module clearfix">
                            <? if (isset($user->rank_image_id)) { ?>
                            <div class="rank_image">
                                	<img src="/file_view.php?id=<?=$user->rank_image_id;?>" height="60" />
                                	<br /><span style="font-size: 10px"><?=$user->getRankInfo()?></span>
                                </div>
                            <? } if (isset($user->user_photo_id)) { ?>
                            <div class="user_image"><img src="/file_view.php?id=<?=$user->user_photo_id;?>" height="70" /></div>
                            <? } ?>
                            <p class="print_page">Page 1 - &#1489;"&#1492;</p>
                            <p class="print_name"><?=$user->rank_name;?> 
                                <?= empty($user->first_he) ? $user->first : $user->first_he;?> 
                                <?= empty($user->last_he) ? $user->last : $user->last_he;?></p>
                            <p class="print_week">
                            	My Missions for the week of <?=$report_name;?>
                            	<br /><span style='font-size: 10px'>
                        		<? 
                        		$date['start'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($start_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
	                        	$date['end'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($end_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
                        		echo "(" . $date['start'] . ' - ' . $date['end'] . ")";
                        		?>
                        		</span>
                            </p>
                            <p class="print_class">Grade: <?=$user->school_class->class_grade;?>-<?=$user->school_class->class_sub;?> : <?=$user->school_class->class_teacher;?></p>
                            <p class="print_sig">Parent's Signature<span></span></p>
                        </div>
                    </div>                      
                    
                    <input type="hidden" name="user_id" class="user_id" value="<?=$user->user_id?>" />
                    
                    <? include("daily_tasks.php"); ?>
                    
                <div class="print_header print_page_two">
                        <div class="marking module clearfix">
                            <p class="print_week"><?=$report_name;?></p>
                            <p class="print_page">Page <span class="page_no">2</span> - &#1489;"&#1492;</p>
                            <p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
                        </div>
                    </div>
                
                <div class="page_template" style="display:none;">
                    <div style="clear:both; height:1px;"></div>
                    <div class="print_content"></div>                   
                </div>
                <div class="multiple">
                    <div class="tasks_page_two">    
                        <? include("weekly_tasks.php"); ?>
                        <? if ($start_date > 2456214) {
                           include("no_label_tasks.php"); } ?>          
                        <? include("shabbos_tasks.php"); ?>                                         
                    </div>
                   
                   <? if ($start_date > 2456214) { 
                        $school_type = $user->school_type_id;
                        if ($school_type == 2 || $school_type == 3) 
                            echo "<input type='hidden' class='child_type' value='All'>";
                        else 
                            echo "<input type='hidden' class='child_type' value='AllDaySchool'>";
                     } ?>
                </div>
                </div>
                
                <? if ($start_date != 2456180 && $start_date < 2456215) { ?>    
                    <div style="clear:both; height:1px;"></div>
                    <div class="print_header print_page_two">
                        <div class="marking module clearfix">
                            <p class="print_week"><?=$report_name;?></p>
                            <p class="print_page">Page <span class="page_no">3</span> - &#1489;"&#1492;</p>
                            <p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
                        </div>
                    </div>
                    
                <div class="multiple">
                <div class="tasks_page_three">
                    <? include("no_label_tasks.php"); ?>
                    <div id="tasks_page_four"></div>
                </div>
                </div>
                
                <div style='page-break-after: always;'></div>                       

                <? } else { ?>
                    <div style='page-break-after: always;'></div>
                <?
                    }
					echo "<div style='page-break-after: always;'>&nbsp;</div>";
                } 
            } 
            ?>    
            </div>
						
</div> <!-- <div class="body"> -->
		
	</body>	
</html>
