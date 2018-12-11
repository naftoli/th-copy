<?
$admin_auth = array('school','user'); 

$d = unixtojd();
$day = date("N");
$start = $d;

switch ($day) 
{
    case 1:
        $start += 6;
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

$start -= 29;
//$start = 2455499;

$message = "";
$report_name = "";

require('header.php'); 

require_once 'class.achosStudent.php';
$as = new AchosStudent($admin_user['admin_id']);
$user_id = $as->getStudentID();
$school_id = 1;
$date_list = "";
$start_date = 0; 
$end_date = 1; 

$days_of_the_week = array("M", "T", "W", "T", "F", "ש", "S");

$today = unixtojd();    
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
    $sunday = $today - $day_of_the_week;
else
    $sunday = $today;
$report_start_date = $sunday + 7;

$schools_select = "";
$classes_select = "";
$users_select = "";

$action = "";
if (isset($_POST['action'])) {
    $action = $_POST['action']; 
            
    if (isset($_POST['date_list'])) {
        $date_list = explode(":", $_POST['date_list']);
        $start_date = $date_list[0]; 
        $end_date = $date_list[1];      
    }

    if ($action == "produce_report") {
    
        include("classes/user.php");
        include("classes/user_track.php");
        include("classes/school_class.php");
        include 'class.taskExceptions.php';
        include("classes/date_tasks_mission.php");
        include("classes/daily_task.php");
        include("classes/weekly_task.php");
        include("classes/shabbos_task.php");
        include("classes/no_label_task.php");
        include("classes/task.php");
        include("classes/date_tasks_mark.php");
        
        $sql = "SELECT * FROM users WHERE user_id=" . $user_id;
        $query = mysql_query($sql);
        $row = mysql_fetch_assoc($query);
        $user = new user($row);
        $user->get_rank();
        $message = $message . $user->first . " " . $user->last;
        $student_count = 1;
        $user->get_school_class();
        $user->get_class_info();
        echo "<input type='hidden' name='1) END DATE' value='" . $end_date . "'>\n";
        $user->get_user_tracks(-1, $start_date, $end_date); 
        echo "<input type='hidden' name='2) # OF DAILY TASKS' value='" . count($user->daily_tasks) . "'>\n";
    }
}

// ***** REPORT DATES ***** //
require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();
// ***** REPORT DATES ***** //
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mark My Scoreboard</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:300,700' rel='stylesheet' type='text/css'>
        <link href="styles/achos.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            @font-face {
                font-family: 'chesstype';
                src: url('fonts/chesstype-webfont.eot');
                src: url('fonts/chesstype-webfont.eot?#iefix') format('embedded-opentype'),
                     url('fonts/chesstype-webfont.woff') format('woff'),
                     url('fonts/chesstype-webfont.ttf') format('truetype'),
                     url('fonts/chesstype-webfont.svg#chesstypechesstype') format('svg');
                font-weight: normal;
                font-style: normal;
            }
            .achos .print_only {
                display:block;
            }
            .achos .print_only #weekly_div { float:none; width:auto; }
			.achos .print_only #daily_div, .achos .print_only #daily_div + .page { float:none; width:auto; }
        </style>
    </head>

    <body>
        
        <? include('admin_header.php'); ?>
        
        <input type="hidden" name="start_date" value="<?=$start_date_greg;?>">
        <input type="hidden" name="end_date" value="<?=$end_date_greg;?>">
        
        <script type="text/javascript" src="scripts/functions.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        
        <script type="text/javascript">
            var school_id = <?=$school_id;?>;
            var start_date = <?=$start_date;?>;
            var end_date = <?=$end_date;?>;
            var check_all = false;
            var check = "";
            var date_task_ids = "";
            var mark_dates = "";
            var checked = false;
            var user_id = <?=$user_id;?>;
            var today = <?=$today;?>;
            var save = false;
            
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
                        
                        //alert(url);
                       $.getJSON(url, function(success) { 
                            if (success == false) {
                                alert("Update not performed.");
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
                                alert("Update not performed.");
                            }
                        });                 
                    }
                })
                // ***** UNCHECK ALL ***** //
                                               
                $(".marking_list #display_submit").hide();                
                $(".sSelect").sSelect();
                $('.print-only').addClass('print_only');
                correctHeight();
                
                <? if (isset($_POST['action']) && $action == 'produce_report') : ?>
                    $("#selectBtn").show();
                <? endif; ?>
                 
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
                    //alert(url);
                    $.getJSON(url, function(success) {  
                        if (success == false) {
                            alert("Update not performed.");
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
                    //if (user_id == 374) alert(url);

                    $.getJSON(url, function(success) {  
                        if (success == false) {
                            alert("Update not performed.");
                        }
                    });                 
                }
            }
                        
            function update_mark(txtbx, date_task_id, mark_date) {
                var user_mark = txtbx.value;
                if (user_mark == '')
                    user_mark = 0;
                
                if (user_mark > 0) 
                {
                    var function_name = "add_mark";
                    var parameters = [user_id, date_task_id, mark_date, user_mark];
                    var url = "add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
                    
                    //alert(url);
                    
                    $.getJSON(url, function(success) 
                    {
                        if (success != 1) 
                            alert("Update not performed. Please try again.");
                    });
                }
                else if (user_mark == 0) 
                {
                    var function_name = "delete_mark";
                    var parameters = [user_id, date_task_id, mark_date];
                    var url = "delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
                    //alert(url);
                    
                    $.getJSON(url, function(success) 
                    {
                        if (success != 1) 
                            alert("Update not performed. Please try again.");
                    });
                }
            }
        </script>
        
        <div class="body left marking_missions achos">
                        
            <H1>Mark My Scoreboard</H1>
            
            <div class="module clearfix generate">
                <p>Mark Scoreboard by choosing your parsha and day of week, then click 'Go'.</p>
            </div>
            
            <form name="date_tasks_report" id="date_tasks_report" action="date_tasks_report_daily.php" method="post" accept-charset="UTF-8">
                <input type="hidden" name="action" id="action" value="">    
                
                <div class="infobox2 marking_list clearfix noprint">
                
                    <!-- ***** WEEKLY PERIOD ***** -->
                    <div class="date_list select_box">                  
                        <a class="prev button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Previous Week')?></span>
                        </a>
                        
                        <select name="date_list" class="sSelect">
                            <? for ($rno = 0; $rno < count($parshos); $rno++) : ?>
                                <? $report = $parshos[$rno]; ?>
                                <? if ( (count($parshos) > 1 && $rno == 0 && $start_date == 0) || ($start_date == $report['start'] && $end_date == $report['end']) ) :  ?>
                                <? $report_name = $report['name']; ?>
                                <option selected value="<?=$report['start'];?>:<?=$report['end'];?>"><?=$report['name'];?> - <?=jdtogregorian($report['start']);?></option>
                                <? else : ?>
                                <option value="<?=$report['start'];?>:<?=$report['end'];?>"><?=$report['name'];?> - <?=jdtogregorian($report['start']);?></option>                                
                                <? endif; ?>
                            <? endfor; ?>
                        </select>
                        
                        <a class="next button">
                            <span class="icon"></span><span class="label"><?=T_('Next Week')?></span>
                        </a>
                    </div>
                    <!-- ***** WEEKLY PERIOD ***** -->
                    
                    <?
                    if (isset($_POST['day'])) {
                    	$chosenDay = $_POST['day'];
                    }
					$days = array(
						0	=>	'Sunday', 
						1	=>	'Monday', 
						2	=>	'Tuesday', 
						3	=>	'Wednesday', 
						4	=>	'Thursday', 
						5	=>	'Friday', 
						6	=>	'שבת'
					);
                    ?>
                    <br /><br /><br />
                    <div class="select_box">
                    	<select name="day" class="sSelect">
                        	<?
                        	foreach ($days as $k => $v) {
                        		if ($k == $chosenDay) {
                        			echo "<option value='" . $k . "' selected>" . $v . "</option>";
                        		} else {
                        			echo "<option value='" . $k . "'>" . $v . "</option>";
                        		}
                        	}
                        	?>
                        </select>
                    </div>
                    <br /><br /><br />
                    
                    <input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='produce_report';">                   
                </div>
                
            </form>
            
            <div id="selectBtn" style="display: none">
                <div class="infobox2 marking_list">
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
            </div>

            <? if ($action == "produce_report") : ?> 
            
            <div class="print-only">
                <div class="print_individual clearfix">                      
                    <div class="print_header">
                        <div class="marking module clearfix">
                            <div class="bh">&#1489;"&#1492;</div>
                            <div class="logo"><img src="images/logo-achos-hatemimim.png" /></div>
                            <?
                            $p = "select photo from admins where admin_id = " . $admin_user['admin_id'];
                            $res = mysql_query($p);
                            $pRow = mysql_fetch_assoc($res);
                            $photo = $pRow['photo'];
                            ?>
                            <div class="user_image"><img src="images/staff/<?=$photo?>" width="149.25" height="189.75"></div>
                            <div class="user_level">Championship: <img src="images/icon-ribbon-colors-<?=$as->getMedal()?>.png" /><br>&#1508;&#1512;&#1513;&#1514; <?=$report_name;?>
</div>
                            <div class="title"><img src="images/bg_title_achos_mission.png" /></div>
                            <div class="user_display first"><span>First Name</span><div class="display"><?=$user->first;?></div></div>
                            <div class="user_display last"><span>Last Name</span><div class="display"><?=$user->last;?></div></div>
                            <div class="user_display grade"><span>Grade</span><div class="display"><?=$user->class_grade . ':' . $user->class_sub?></div></div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="user_id" class="user_id" value="<?=$user->user_id?>" />
                    
                    <div id="tasks_div">
            			
            			<? include("daily_tasks_daily.php"); ?>
                        <? include("weekly_tasks.php"); ?>                                      
                        
                    </div>
                                
                </div>
                    
            </div>
                
            <? endif; ?>
            <!-- if ($action == "produce_report") : --> 
                        
        </div> <!-- <div class="body"> -->
        
    </body> 
</html>
