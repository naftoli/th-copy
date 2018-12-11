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

$start -= 7;
//$start = 2455499;

$message = "";
$report_name = "";

require('header.php');

$date_list = "";

require_once 'class.achosStudent.php';
$users = array();
if ($admin_user['auth'] != 'super') {
	$as = new AchosStudent($admin_user['admin_id']);
	$users[] = $as->getStudentID();
	$school_id = $as->getSchoolID();
} else {
	$school_id = $admin_user['auths']['school'][0];
	$sql = "select user_id from users where school_id = " . $school_id . " and print_missions = 1";
	if (isset($_POST['grade'])) {
		$sql .= " and class_id = " . mysql_real_escape_string($_POST['grade']);
	}
	//$sql = "select user_id from users where school_id = " . $school_id;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$users[] = $row['user_id'];
	}
}
//echo "<pre>"; print_r($users); echo "</pre>"; exit;

$days_of_the_week = array("M", "T", "W", "T", "F", "ש", "S");

$today = unixtojd();    
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
    $sunday = $today - $day_of_the_week;
else
    $sunday = $today;
$report_start_date = $sunday;

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
        include("class.taskExceptions.php");
        include("classes/date_tasks_mission.php");
        include("classes/daily_task.php");
        include("classes/weekly_task.php");
        include("classes/shabbos_task.php");
        include("classes/no_label_task.php");
        include("classes/task.php");
        include("classes/date_tasks_mark.php");
		
		$sheets = array();
		foreach ($users as $user_id) {
			$sql = "select admin_id from admin_auths where auth = 'user' and role_id = 1 and id = " . $user_id;
	        $result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$adminID = $row['admin_id'];
				
		        $sql = "SELECT * FROM users WHERE user_id=" . $user_id;
		        $query = mysql_query($sql);
		        $row = mysql_fetch_assoc($query);
		        $user = new user($row);
		        $message = $message . $user->first . " " . $user->last;
		        $student_count = 1;
		        $user->get_school_class();
		        $user->get_class_info();
		        $user->get_user_tracks(-1, $start_date, $end_date); 
	        
	        	$sheets[$adminID] = $user;
			}
		}
    }
}
/*
if (isset($sheets)) {
	foreach ($sheets as $id => $user) {
		echo 'Admin id: ' . $id . "<br />";
		echo 'User id: ' . $user->user_id;
	}
}
 * 
 */

// ***** REPORT DATES ***** //
require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();
// ***** REPORT DATES ***** //
?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Print My Scoreboard</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href='//fonts.googleapis.com/css?family=Yanone+Kaffeesatz:300,700' rel='stylesheet' type='text/css'>
        <link href="styles/achos.css?v=1.6" rel="stylesheet" type="text/css">
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
				font-family: 'Yanone Kaffeesatz', sans-serif;
				font-weight:300;
				height:0;
				overflow:hidden;
			}

        </style>
    </head>

    <body>
        
        <? include('admin_header.php'); ?>
        
        <script type="text/javascript" src="scripts/functions.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
        
        <script type="text/javascript">
            $( function(){
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
                
                $('.generate:last').hide().before('<div class="module clearfix generate loading"><div class="loader"></div><h3>Processing...</h3></div>');          
            });

        $(window).bind('load', function() {
            $('.print-only').addClass('print_only');
            $('.print-only').css('opacity',1);
            $('.generate.loading').hide();
            $('.generate:last').show();
            correctHeight();
        });
        </script>
        
        <div class="body left marking_missions achos">
                        
            <H1>Print My Scoreboard</H1>
            
            <div class="module clearfix generate noprint">
                <p>Generate Scoreboard by choosing your parsha and clicking 'Go'.</p>
            </div>
            
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
                                <? if ( (count($parshos) == 1) || ($report_start_date >= $report['start'] && $report_start_date <= $report['end']) || ($start_date == $report['start'] && $end_date == $report['end']) ) :  ?>
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
                    
                    <? if ($admin_user['auth'] == 'super') : ?>
                    <?
                    $grades = array();
                    $sql = "select class_id, class_grade, class_sub 
                    		from classes 
                    		where class_era = 0 
                    		and school_id = 1  
                    		order by class_grade, class_sub";
					//echo $sql;
					$result = mysql_query($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$grades[] = $row;
					}
                    ?>
                    	<br /><br /><br />
                    	<div class="date_list select_box"> 
                    		<select name='grade'>
                    			<?
                    			foreach ($grades as $grade) {
                    				if (isset($_POST['grade']) && $grade['class_id'] == $_POST['grade']) {
                    					echo "<option value=" . $grade['class_id'] . " selected='selected'>" . 
	                    					$grade['class_grade'] . '-' . $grade['class_sub'] . 
	                    					"</option>";
                    				} else {									
	                    				echo "<option value=" . $grade['class_id'] . ">" . 
	                    					$grade['class_grade'] . '-' . $grade['class_sub'] . 
	                    					"</option>";
									}
                    			}
                    			?>
                    		</select>
                    	</div>
					<? endif; ?>
                    
                    <input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='produce_report';">                   
                </div>
            </form>
            
            <? if ($action == "produce_report") : ?>
            
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
                                        <p>Step 4: In the second tab set all Margins to 0.0 inches (All Sides)</p>
                                        <p>Step 5: Set all Headers & Footers to Blank</p>
                                        <p>Note: The browser will save these preferences for later use.</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="module clearfix generate">
                        <h3>Scoreboard generated</h3>
                        <p><a href="javascript:window.print()" class="button">Print</a></p>
                    </div>
                </div>
				
				<? if (empty($sheets)) echo "No users match your criteria."; ?>
                <div class="print-only">
					<? foreach ($sheets as $id => $user) : ?> 
	                    <div class="print_individual clearfix">                      
		                    <div class="print_header">
		                        <div class="marking module clearfix">
									<div class="bh">&#1489;"&#1492;</div>
									<div class="logo"><img src="images/logo-achos-hatemimim.png" /></div>
									<?
									$p = "select photo from admins where admin_id = " . $id;
		                            $res = mysql_query($p);
		                            $pRow = mysql_fetch_assoc($res);
		                            $photo = $pRow['photo'];
									?>
									<div class="user_image"><img src="images/staff/<?=$photo?>" width="149.25" height="189.75"></div>
                            <div class="user_level">Week Of:<!--<img src="images/icon-ribbon-colors-<?=$as->getMedal()?>.png" />-->
                            	<br>&#1508;&#1512;&#1513;&#1514; <?=$report_name;?>
							</div>
                            <!--<div class="title"><img src="images/bg_title_achos_mission.png" /></div>-->
                            <br />
                            <div class="user_display first"><span>First Name</span><div class="display"><?=$user->first;?></div></div>
                            <div class="user_display last"><span>Last Name</span><div class="display"><?=$user->last;?></div></div>
                            <div class="user_display grade"><span>Grade</span><div class="display"><?=$user->class_grade . ':' . $user->class_sub?></div></div>
                            <div class="user_display grade" style="float: right; width: 8%;"><span>Level</span><div class="display"><?=$as->getLevel()?></div></div>
		                        </div>
		                    </div>
		                    
		                    <input type="hidden" name="user_id" class="user_id" value="<?=$user->user_id?>" />
		            
							<? include("weekly_tasks.php"); ?>                                      
		                    <? include("daily_tasks.php"); ?>
		                                
		                </div>
					<? endforeach; ?>
                </div>
                
            <? endif; //if ($action == "produce_report") : ?>
