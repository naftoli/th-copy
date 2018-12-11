<? 
//header("Location: under_construction.php");
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
require_once('calendar.php');
include("classes/subject.php");

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
        <title>Print Mission Sheets - Tzivos Hashem Management System</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:300,700' rel='stylesheet' type='text/css'>
        <style type="text/css">
            .achos .print_only {
                display:block;
				font-family: 'Yanone Kaffeesatz', sans-serif;
            }
            .achos .print_only {
                display:block;
				font-family: 'Yanone Kaffeesatz', sans-serif;
            }
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

                var content_height = 980;
                var content_height_one = 950;
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
                };

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
                            columns: 1,
                            target: '.print_individual:eq('+index+') .page:last .print_content',
                            overflow: {
                                height: content_height_one,
                                id: ".print_individual:eq(" + index + ") #daily_div",
                                doneFunc: function(){
                                    //console.log("done with page");
                                    buildNewsletter(index);
                                    
                                    //add blank page for children that have three pages in order not to mess up dbl sided printing
                                    //var dblSided = confirm( "Are you planning to print your mission sheets double sided?\nIf yes click 'OK', otherwise click 'Cancel'" );
                                    //if ( page_no % 2 == 0 ) {
                                    //    $(".page:last").after("<div class='page' style='display: block'>&nbsp;</div>");
                                    //}
                                    
                                }
                            }
                        });
                        
                        //fix for pre1a showing going to sleep before waking up
                        //if ( $(element).find('#daily_div').contents().length < 40 )
                        //    $(element).find('#daily_div').insertAfter('.print_individual:eq('+index+') .page .print_content:first');
                        
                    } else if ($(element).find('.tasks_page_two').contents().length > 0) {
                        $page = $(element).find(".page_template").clone().addClass("page").removeClass("page_template").css("display", "block");
                    
                        $page.find(".print_header .page_no").text(page_no);
                        $(element).append($page);
                        page_no++;
                        
                        $(element).find('.tasks_page_two').columnize({
                            columns: 2,
                            target: ".print_individual:eq(" + index + ") .page:last .print_content",
                            lastNeverTallest: true,
                            overflow: {
                                height: content_height,
                                id: ".print_individual:eq(" + index + ") .tasks_page_two",
                                doneFunc: function(){
                                    //console.log("done with page");
                                    buildNewsletter(index);
                                }
                            }
                        });
                        
                    }
                };

                $('.print-only').css('opacity',0);
                $('.generate:last').hide().before('<div class="module clearfix generate loading"><div class="loader"></div><h3>Processing...</h3></div>');

                donePrepare = function(index) {
                    $('.print-only').addClass('print_only');
                    $('.print-only').css('opacity',1);
                    $('.generate.loading').hide();
                    $('.generate:last').show();
                    correctHeight();
                };
                
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
        
        <div class="body left marking_missions achos">
                        
            <H1>Print Mission Sheets</H1>
            
            <form name="date_tasks_report" id="date_tasks_report" action="date_tasks_print.php" method="post" accept-charset="UTF-8">
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
                                <? if ( (count($parshos) > 1 && $rno == 3 && $start_date == 0) || ($start_date == $report['start'] && $end_date == $report['end']) ) :  ?>
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
                    
                    <br /><br />
                    <center>
                        <input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='produce_report';">                   
                    </center>                   
                </div>
                
            </form>

                <div class="noprint">
                    <div class="module clearfix generate">
                        <p>Generate Mission Sheets by choosing your parsha.</p>
                    </div>
                    
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
                    <div class="print_individual">                      
                    <div class="print_header">
                        <div class="marking module clearfix">
                            <? if (isset($user->rank_image_id)) { ?>
                            <div class="rank_image"><img src="/file_view.php?id=<?=$user->rank_image_id;?>" height="70" /></div>
                            <? } if (isset($user->user_photo_id)) { ?>
                            <div class="user_image"><img src="/file_view.php?id=<?=$user->user_photo_id;?>" height="70" /></div>
                            <? } ?>
                            <p class="print_page">Page 1 - &#1489;"&#1492;</p>
                            <p class="print_name"><?=$user->rank_name;?> 
                                <?= empty($user->first_he) ? $user->first : $user->first_he;?> 
                                <?= empty($user->last_he) ? $user->last : $user->last_he;?></p>
                            <p class="print_week">My Missions for the week of <?=$report_name;?></p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="user_id" class="user_id" value="<?=$user->user_id?>" />
            
                    <? include("daily_tasks.php"); ?>
                                
                    <div class="page_template">
                        <div style="clear:both; height:1px;"></div>
                        <div class="print_header print_page_two">
                            <div class="marking module clearfix">
                                <p class="print_week"><?=$report_name;?></p>
                                <p class="print_page">Page <span class="page_no">2</span> - &#1489;"&#1492;</p>
                                <p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
                            </div>
                        </div>
                        <div class="print_content"></div>                      
                                            
                    </div>
                    <div style='page-break-after: always;'></div>
        
                        <div class="tasks_page_two">    
                            <? include("weekly_tasks.php"); ?>                                      
                        </div>
                                                               
                </div>
                    
                </div>
                
            <? endif; ?>
            <!-- if ($action == "produce_report") : --> 
