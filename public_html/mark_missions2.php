<?php 
//header("Location: under_construction.php"); exit;
$admin_auth = array('school','user'); 

$d = unixtojd();
$day = date("N", jdtounix($d));
$end = $d;

switch ($day) {
    // 1 is monday, 7 is sunday
    case 1:
        $end -= 4;
        break;
    case 2:
        $end -= 5;
        break;
    case 3:
        $end -= 6;
        break;
    case 4:
        break;
    case 5:
		$end -= 1;
        break;
    case 6:
        $end -= 2;
        break;
    case 7:
		$end -= 3;
        break;
    default:
        break;
}
//$start = ($end - 34);
//$start = $end - 118;
$start = 2457928;

/*
require_once 'class.globalSettings.php';
$dates = GlobalSettings::getCurYearDates();
$start = $dates['start'];
$end = $dates['end'];
*/
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
$admin = new \classes\admin($row);
if ($admin->auth != "super") {
    $admin->get_schools();
    if (count($admin->schools) == 1) {
    	//print_r($admin->schools);
        $school_id = $admin->schools[0]->school_id;
    }
}

$class_id = 0;
$user_id = 0;
$date_list = "";
$start_date = 0; 
$end_date = 1; 
$users = array();
/*
$today = unixtojd();    
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
    $sunday = $today - $day_of_the_week;
else
    $sunday = $today;
$report_start_date = $sunday + 7;
*/
$report_start_date = $end - 13;

$schools_select = "";
$classes_select = "";
$users_select = "";

$action = "";
$user_lookup_error = false;

$class_id = 0;
$user_id = 0;

if (isset($_POST['action'])) {
    $action = $_POST['action']; 
    
    $school_id = $_POST['school_id'];   
    
    if (isset($_POST['class_id'])) $class_id = $_POST['class_id'];
            
    if (isset($_POST['user_id'])) $user_id = $_POST['user_id'];
            
    if (isset($_POST['date_list'])) {
        $date_list = explode(":", $_POST['date_list']);
        $start_date = $date_list[0]; 
        $end_date = $date_list[1];      
    }
            
    get_classes_select($school_id, $class_id);
    get_users_select($school_id, $class_id, $user_id);
	
	if ($action == 'user_lookup') {
		if ($_POST['userLookup']) {
			$user_id = intval($_POST['userLookup']);
			$sql = "SELECT user_id, school_id, class_id FROM users WHERE (user_id = " . $user_id . " OR user_serial = " . $user_id . ")";
			
			if($admin_user['auth'] != 'super') {
				$sql .= " AND school_id = $school_id";
			}
			
			$result = mysql_query($sql);
			if (mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$user_id = $row['user_id'];
				$school_id = $row['school_id'];
				$class_id = $row['class_id'];
				$start_date = $end - 6;
				$end_date = $end;
				$action = "produce_report";
			} else {
				$user_lookup_error = true;
			}
		}
	}
    if ($action == "produce_report") {
		//$showDate = $_POST['showDate'];
		//$dblSided = $_POST['dblSided'];
		//find out if user needs english or yiddish missions
		$sql = "select lang_id from users where user_id = " . $user_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$lang = $row['lang_id'];
		if ($lang == 2) {
			header("Location: mission_report/newSchoolMark.php?user=$user_id&school=$school_id&grade=$class_id&start=$start_date&end=$end_date&he=1");
		} else {
			header("Location: mission_report/newSchoolMark.php?user=$user_id&school=$school_id&grade=$class_id&start=$start_date&end=$end_date");
		}
		exit;
	} 
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
    //echo $sql;
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
    $classes_select = $classes_select . "<option value='0'>Choose a Platoon</option>";
    
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
if(mysql_num_rows($schools_query) == 1){
	$school = mysql_fetch_assoc($schools_query);
	$school_id = $school['school_id'];
	get_classes_select($school_id, $class_id);
    get_users_select($school_id, $class_id, $user_id);
}


if ($school_id > 0) 
{
    // ***** REPORT DATES ***** //
    include("classes/report.php");
    $reports = array();
    $sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' and start_date >= $start and end_date <= $end ORDER BY start_date";    
    $query = mysql_query($sql);
	//echo '<input type="hidden" value="'.$sql.'"/>';
    while ($row = mysql_fetch_assoc($query)) {
        $report = new report($row);
        //hide pesach 2014
        //if ($report->start_date == 2456761 || $report->start_date == 2456768) continue;
		//if ($report->start_date >= 2456927 && $admin_user['auth'] != 'super') continue;
        array_push($reports, $report);
    }
    // ***** REPORT DATES ***** //
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mark Mission Sheets - Tzivos Hashem Management System</title>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    </head>

    <body>
            
        <? include('admin_header.php'); ?>
        
        <div class="body left marking_missions">
                        
            <H1>Mark Mission Sheets</H1>
            
            <div class"module">
        		<a href="date_tasks_report_new.php">Take me back to the OLD STYLE mission sheets for marking!</a><br />
        		Click <a href="settings.php?admin_id=<?=$admin->admin_id?>&school_id=<?=$school_id?>">here</a> to change mission settings</a>
        	</div>
            
            <form name="date_tasks_report" id="date_tasks_report" action="mark_missions2.php" method="post" accept-charset="UTF-8">

                <input type="hidden" name="action" id="action" value="">    
                
                <div class="infobox2 marking_list clearfix noprint">
                
                    <div class="school_list select_box">
                        <a class="prev button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Previous School')?></span>
                        </a>
                    
                        <SELECT name="school_id" id="school_id">
							<? if($school) {?>
								<OPTION selected value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
							<? } else { ?>
							<OPTION value="-1">Please select a school</OPTION>
								<? while ($school = mysql_fetch_assoc($schools_query)) : ?>
									<? if ($school_id == $school['school_id']) : ?>
										<OPTION selected value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
									<? else : ?>
										<OPTION value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
									<? endif;
								endwhile;
							} ?>
                            
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
                    <? if ($reports) : ?>
                    <div class="date_list select_box">                  
                        <a class="prev button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Previous Week')?></span>
                        </a>
                        
                        <select name="date_list" class="sSelect">
                            <? $numReports = count( $reports ); ?>
                            <? for ($rno = 0; $rno < $numReports; $rno++) : ?>
                                <? $report = $reports[$rno]; ?>
                                <? if ( $rno == ($numReports - 1) || ($start_date == $report->start_date && $end_date == $report->end_date) ) :  ?>
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
                    
                    <? if ($school_id > 0 || $school) : ?>
                    <center>
                        <input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='produce_report';return checkForUser();">                   
                    </center>                   
                    <? endif; ?>
                </div>
								
				<div class="marking_list clearfix noprint" style="margin-top: -10px;">
					OR Enter User ID / Serial Number: <input type="text" name="userLookup" />
					<input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='user_lookup';">
				</div>
				<? if ($user_lookup_error) {?>
					<span style="color: red;">Error: Invalid User ID / Serial Number</span>
				<?}?>
                
            </form>
                           
		</div>

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
        
        $('.slider:last .list_expand li h3').nextAll().hide();
        $('.slider:last .list_expand li h3').click(function(){
            $(this).nextAll().slideToggle('fast');
            $(this).parents('li').toggleClass('open');
        }); 
    });
    
    function checkForUser() {
    	var user_id = $("#user_id").val();
    	if (user_id == -1) {
    		alert("You must choose a student.");
    		return false;
    	}
    }
</script>
        	
</body>
</html>
                