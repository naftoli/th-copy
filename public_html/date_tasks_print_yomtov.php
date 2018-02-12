<? 
//header("Location: under_construction.php");
$admin_auth = array('school','user'); 

$message = "";
$student_count = 0;

$report_name = "";
$number_of_students = 0;
$change_school = false;
if (isset($_POST['change_school'])) {
    $change_school = $_POST['change_school'];
}

require('header.php'); 
require_once('calendar.php');
include("classes/subject.php");

$school_id = 0;

if (isset($_POST['school_id']))
    $school_id = $_POST['school_id'];
    
include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
if ($admin->auth != "super") {
    $admin->get_schools();
    if (count($admin->schools) == 1) {
        $school_id = $admin->schools[0]->school_id;
    }
}

$class_id = 0;
$user_id = 0;
$date_list = "";
 
$users = array();

$days_of_the_week = array("ש", "S", "M", "T", "W", "T", "F");

$schools_select = "";
$classes_select = "";
$users_select = "";

$action = "";
$students = array();
$student_count = 0;

//rosh hashana 5778
$start = 2458012; 
$end = 2458025;

if (isset($_POST['action'])) {
    $action = $_POST['action']; 
    
    $school_id = $_POST['school_id'];   
    
    if (isset($_POST['class_id'])) 
        $class_id = $_POST['class_id'];
    else
        $class_id = 0;
            
    if (isset($_POST['user_id'])) 
        $user_id = $_POST['user_id'];
    else
        $user_id = 0;
         
    if (isset($_POST['date_list'])) {
        $date_list = explode(":", $_POST['date_list']);
        $start_date = $date_list[0]; 
        $end_date = $date_list[1];      
    }
            
    get_classes_select($school_id, $class_id);
    get_users_select($school_id, $class_id, $user_id);

    if ($action == "produce_report") {
        	
    	require_once 'class.taskExceptions.php';
        include("classes/user.php");
        include("classes/user_track.php");
        include("classes/school_class.php");
        include("classes/date_tasks_mission.php");
        include("classes/daily_task.php");
        include("classes/weekly_task.php");
        include("classes/shabbos_task.php");
        include("classes/no_label_task.php");
        include("classes/task.php");
        include("classes/date_tasks_mark.php");
        
        //set dates
        $dates['start'] = array( 2458012, 2458019 );
        $dates['end'] = array( 2458018, 2458025 );
        
        //get report names
        $report_names = array();
        $sql = "SELECT report_name FROM reports 
            WHERE report_type='mission_cover_sheet' 
            AND visibility != 'none' 
            and start_date >= $start   
            and end_date <= $end   
            ORDER BY start_date"; 
        //echo $sql;   
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $report_names[] = $row['report_name']; 
        }
                
        if ($user_id > 0) {
            $sql = "SELECT * FROM users WHERE user_id=" . $user_id;
        }
        else {
            if ($class_id > 0) {
                $sql = "SELECT * FROM users WHERE school_id=" . $school_id . " AND class_id=" . $class_id . " and user_registered > 0 order by last, first";
            }
            else {
                $sql = "SELECT * FROM users u 
                        join classes c using (class_id) 
                        WHERE u.school_id=" . $school_id . " 
                        and u.user_registered > 0 
                        order by c.class_grade, c.class_sub, u.last, u.first";
            }
        }
        
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $student_count++; 
            for ( $i = 0; $i < count($dates['start']); $i++ ) { 
                $students[] = generateMissions( $row, $dates['start'][$i], $dates['end'][$i], $report_names[$i] );
            }
        }
    }
}

function generateMissions( $row, $start_date, $end_date, $report_name ) { 
    $user = new user($row);
    $user->get_school_class();
    $user->get_rank();
    $user->get_user_tracks(-1, $start_date, $end_date);             
    return array($report_name => $user);
}

function get_users_select($school_id, $class_id, $user_id) {
    global $users_select;
    
    $sql = "SELECT u.user_id, u.first, u.last, u.class_id, c.class_grade, c.class_sub ";
    $sql = $sql . "FROM users AS u ";
    $sql = $sql . "JOIN classes AS c USING (class_id) ";
    $sql = $sql . "WHERE u.school_id=" . $school_id . " and u.user_registered > 0 ";
    if ($class_id > 0)
        $sql = $sql . "AND class_id=" . $class_id . " ";
    $sql = $sql . "ORDER BY u.class_id, u.last, u.first";
    $query = mysql_query($sql);
    
    $users_select = "<div class='user_list select_box'>";
    $users_select = $users_select . "<a class='prev button'>";
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Previous Student</span>";
    $users_select = $users_select . "</a>";
    $users_select = $users_select . "<select name='user_id' id='user_id' class='sSelect'>";
    $users_select = $users_select . "<option value='-1'>All students</option>";
        
    while ($row = mysql_fetch_assoc($query)) {
        $grade = $row['class_grade'];
        if ($row['class_sub'] != "")
            $grade = $grade . "-" . $row['class_sub'];
            
        if ($user_id == $row['user_id'])
            $users_select = $users_select . "<option selected value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";
        else
            $users_select = $users_select . "<option value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";       
    }

    $users_select = $users_select . "</select>";
    $users_select = $users_select . "<a class='next button'>";
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Next Student</span>";
    $users_select = $users_select . "</a>";
    $users_select = $users_select . "</div>";
}

function get_classes_select($school_id, $class_id) {
    global $classes_select;
    
    $sql = "SELECT * FROM classes WHERE school_id=" . $school_id . " and class_era = 0 order by class_grade, class_sub";
    $query = mysql_query($sql);
    
    $classes_select = "<div class='class_list select_box'>";
    $classes_select = $classes_select . "<a class='prev button'>";
    $classes_select = $classes_select . "<span class='icon'></span>";
    $classes_select = $classes_select . "<span class='label'>Previous Platoon</span>";
    $classes_select = $classes_select . "</a>";
    $classes_select = $classes_select . "<select name='class_id'>";
    $classes_select = $classes_select . "<option>Choose a Platoon</option>";
    
    while ($row = mysql_fetch_assoc($query)) {      
        if ($class_id == $row['class_id']) 
            $classes_select = $classes_select . "<option selected value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
        else
            $classes_select = $classes_select . "<option value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
    }
    
    $classes_select = $classes_select . "</select>";
    $classes_select = $classes_select . "<a class='next button'>";
    $classes_select = $classes_select . "<span class='icon'></span>";
    $classes_select = $classes_select . "<span class='label'>Next Platoon</span>";
    $classes_select = $classes_select . "</a>";
    $classes_select = $classes_select . "</div>";
}


// ***** SCHOOLS ***** //
if ($admin->auth == "super") {
    $schools_sql = "SELECT school_id, school_name FROM schools ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
elseif (count($admin->schools) > 0) 
{
    $schools_sql = "SELECT s.school_id, s.school_name FROM schools AS s JOIN admin_auths AS aa ON (aa.admin_id=" . $admin->admin_id . " AND aa.auth='school' AND aa.id=s.school_id) ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
// ***** SCHOOLS ***** //

if ($school_id > 0) 
{
    // ***** REPORT DATES ***** //
    include("classes/report.php");
    $reports = array();
    $sql = "SELECT * FROM reports 
            WHERE report_type='mission_cover_sheet' 
            AND visibility != 'none' 
            and start_date >= $start 
            and end_date <= $end 
            ORDER BY start_date"; 
    //echo $sql;   
    $query = mysql_query($sql);
    while ($row = mysql_fetch_assoc($query)) {
        $report = new report($row);
        array_push($reports, $report);
    }
    // ***** REPORT DATES ***** //
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Print Mission Sheets - Tzivos Hashem Management System</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            .dedication {
                font-family:"Myriad Pro",Arial,Helvetica,sans-serif;
                font-size: 15px;
                text-align: center;
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
            .multiple {
                -moz-column-count: 2;
                -moz-column-gap: 25px;
            }
        </style>

    </head>

    <body>
            
        <? include('admin_header.php'); ?>
        
        <script type="text/javascript" src="scripts/functions.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
        
        <script type="text/javascript">
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
                
                // ***** SCHOOL LIST CHANGE ***** //
                $(".school_list select").sSelect().change(function () {
                    document.getElementById('action').value = "get_selects";
                    $('#date_tasks_report').submit();
                })
                // ***** SCHOOL LIST CHANGE ***** //
                
                // ***** CLASS LIST CHANGE ***** //
                $(".class_list select").sSelect().change(function () {
                    document.getElementById('action').value = "get_selects";
                    $('#date_tasks_report').submit();
                })
                // ***** CLASS LIST CHANGE ***** //
                
                // ***** USER LIST CHANGE ***** //
                $(".user_list select").sSelect().change(function () {
                    //if (number_of_students > 0)
                    //  $(this).closest('form').submit();
                })
                // ***** USER LIST CHANGE ***** //
                
                // ***** WEEKLY PERIOD CHANGE ***** //
                $(".date_list select").sSelect().change(function () {
                    //if (number_of_students > 0)
                    //  document.forms["date_tasks_report"].submit();
                })
                // ***** WEEKLY PERIOD CHANGE ***** //
                                
                $(".marking_list #display_submit").hide();
                
                $('.marking.module').addClass('dontsplit');
                //$('.tasks_page_two').columnize({ columns: 2 });

                $('.slider:last .list_expand li h3').nextAll().hide();
                $('.slider:last .list_expand li h3').click(function(){
                    $(this).nextAll().slideToggle('fast');
                    $(this).parents('li').toggleClass('open');
                });

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
        
        function doAlert() {
            alert('Important Notice: Please be advised that the page may take a long time to load. Please be patient and wait until it is completely loaded.');
            //alert('Important Notice: Please be advised that the page may take a long time to load.\nDO NOT PANIC!\nJust WAIT until the browser is finished loading.\nIf an error message pops up just click on \'do not ask me again\' and then click \'continue\'.\n If the browser says \'not responding\' JUST WAIT!');
        }
        </script>
        
        <div class="body left marking_missions">
                        
            <H1>Print Mission Sheets</H1>
            
            <div class="infobox noprint">
				Rosh Hashana includes 2 weeks of missions - Nitzavim/Vayelech, Haazinu.
                <!--Yom Kippur missions include 3 weeks worth of missions - Yom Kippur, Succos, Shabbos Breishis.-->
            </div>
            
            <form name="date_tasks_report" id="date_tasks_report" action="date_tasks_print_yomtov.php" method="post" accept-charset="UTF-8">
                <input type="hidden" name="action" id="action" value="">    
                
                <div class="infobox2 marking_list clearfix noprint">
                
                    <div class="school_list select_box">
                        <a class="prev button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Previous School')?></span>
                        </a>
                    
                        <SELECT name="school_id" id="school_id">
                            <OPTION value="-1">Please select a school</OPTION>
                            <? while ($school = mysql_fetch_assoc($schools_query)) : ?>
                            
                                <? if ($school_id == $school['school_id']) : ?>
                                    <OPTION selected value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
                                <? else : ?>
                                    <OPTION value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
                                <? endif; ?>
                            
                            <? endwhile; ?>
                        </SELECT>
                        
                        
                        <a class="next button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Next School')?></span>
                        </a>                        
                    </div>
                
                    <div id="class_list_div" name="class_list_div">
                        <?=$classes_select;?>
                    </div>
                    
                    <div id="user_list_div" name="user_list_div">
                        <?=$users_select;?>
                    </div>
                    
                    <!-- ***** WEEKLY PERIOD ***** -->
                    <? if ($school_id > 0) : ?>
                    <div class="date_list select_box">                  
                        <a class="prev button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Previous Week')?></span>
                        </a>
                        
                        <select name="date_list" class="sSelect">
                            <? for ($rno = 0; $rno < count($reports); $rno++) : ?>
                                <? $report = $reports[$rno]; ?>
                                <? if ( (count($reports) > 1 && $rno == 3 && $start_date == 0) || ($start_date == $report->start_date && $end_date == $report->end_date) ) :  ?>
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
                    <? endif; ?>
                    <!-- ***** WEEKLY PERIOD ***** -->
                    
                    <? if ($school_id > 0) : ?>
                    <center>
                        <input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='produce_report';doAlert();">                   
                    </center>                   
                    <? endif; ?>
                </div>
                
            </form>

                <? if ($school_id > 0) : ?>         
                <div class="noprint">
                    <div class="module clearfix generate">
                        <p>Generate Mission Sheets by choosing an option from all the fields above.</p>
                    </div>
                <? endif; ?>                
                    
            <? if ($action == "produce_report") : ?>            
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
                                        <p>Step 4: In the second tab set all Margins to 0.0 inches (All Sides)</p>
                                        <p>Step 5: Set all Headers & Footers to Blank</p>
                                        <p>Note: The browser will save these preferences for later use.</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="module clearfix generate">
                        <h3>Mission Sheets were generated<br/>for <?=$student_count;?> Student(s)</h3>
                        <p><a href="javascript:window.print()" class="button">Print</a></p>
                    </div>
                </div>

                <!-- ********** <div class="print_only"> ********** -->
                <!--<div>-->
                <div class="print-only">
                <? 
                foreach ( $students as $users ) {
                    foreach ( $users as $report_name => $user ) {
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
                            	My Missions for Shabbos <?=$report_name;?> and following week
                            	<br /><span style='font-size: 10px'>
                        		<? 
                        		$date['start'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($user->user_tracks[0]->start_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
	                        	$date['end'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($user->user_tracks[0]->end_date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
                        		echo "(" . $date['start'] . ' - ' . $date['end'] . ")";
								$heDates = array();
								$temp = $user->user_tracks[0]->start_date;
								$ctr = 13;
								do {
									$he = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($temp, true, CAL_JEWISH_ADD_GERESHAYIM));
									$heArr = explode(' ', $he);
									$heDates[$ctr++] = $heArr[0] . ' ' . $heArr[1];
								} while (++$temp <= $user->user_tracks[0]->end_date)
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
                
            <? endif; ?>
            <!-- if ($action == "produce_report") : --> 
