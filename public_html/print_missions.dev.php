<?
/***************** CONFIGURATION **********************/
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
//header("Location: under_construction.php");

/***************** AUTHENTICATION **********************/
$admin_auth = array('school','user');
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** MODIFY THE DATE **********************/
$d = unixtojd(); // get the current date
$day = date("N", jdtounix($d)); // get the day of the week from the unix timestamp
$end = $d; // that is the end date
// modify the date, not 100% sure how this is supposed to work...
switch ($day) {
    case 1:
        $end += 3;  break;
    case 2:
        $end += 2;  break;
    case 3:
        $end += 1;  break;
    case 4:
        break;
    case 5:
		$end += 6;  break;
    case 6:
        $end += 5;  break;
    case 7:
		$end += 4;  break;
    default:
        break;
}
// unknown (unused) variables
$message = "";
$student_count = 0;

$report_name = "";
$number_of_students = 0;

/***************** CHANGE THE SCHOOL **********************/
$change_school = false;
if (isset($_POST['change_school'])) {
    $change_school = $_POST['change_school'];
}
/***************** IMPORTS **********************/
require_once('calendar.php');
include("classes/subject.php");

/***************** GET THE START AND END DATES **********************/
require_once 'class.globalSettings.php';
$dates = GlobalSettings::getCurYearDates();
$start = $dates['start'];
$end = $dates['end'];

// default the school id to 0
$school_id = 0;
if (isset($_POST['school_id']))
    $school_id = $_POST['school_id'];
    
/***************** GET THE ADMIN INFORMATION **********************/
include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row); // set up the admin from the DBS row

// if the admin is not a super set the school id to their school id
if ($admin->auth != "super") {
    $admin->get_schools();
    if (count($admin->schools) == 1) {
    	//print_r($admin->schools);
        $school_id = $admin->schools[0]->school_id;
    }
}
// variables for the defulat data
$class_id = 0; // the class selected
$user_id = -1; // the user selected. defaults to all/-1
$date_list = "";
$start_date = 0; // the start date
$end_date = 1; // end date
$users = array(); // list of users
/*
$today = unixtojd();    
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
    $sunday = $today - $day_of_the_week;
else
    $sunday = $today;
$report_start_date = $sunday + 7;
*/
//$report_start_date = $end - 6;
/***************** SELECT DROPDOWN **********************/
$schools_select = "";
$classes_select = "";
$users_select = "";
$action = "";

/***************** HANDLE THE ACTION **********************/
if (isset($_POST['action'])) {
    $action = $_POST['action']; // get the action
    // get the school id
    $school_id = $_POST['school_id'];   
    // update the class_id if it is in the post paramaters. set to 0 on line 77
    if (isset($_POST['class_id'])) {
        $class_id = $_POST['class_id'];
    } else if ($action == "produce_report") {
		$class_id = ["-1"]; // the class selected
	}
    // update the user_id if it is in the post paramaters. set to 0 on line 78
    if (isset($_POST['user_id'])) 
        $user_id = $_POST['user_id'];
    // get the date from the post params
    if (isset($_POST['start_date_list']) && isset($_POST['end_date_list'])) {
        //$date_list = explode(":", $_POST['date_list']);
        $start_date = $_POST['start_date_list'];
        $end_date = $_POST['end_date_list'];
    }
	// redirect to the printout
    if ($action == "produce_report") {
    	$showDate = $_POST['showDate'];
		$dblSided = $_POST['dblSided'];
		/*
		//find out if user needs english or yiddish missions
		$sql = "select lang_id from users where user_id = " . $user_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$lang = $row['lang_id'];
		if ($lang == 2) {
			header("Location: mission_report/newSchoolPrint.php?user=$user_id&school=$school_id&grade=$class_id&start=$start_date&end=$end_date&showDate=$showDate&dblSided=$dblSided&he=1");
		} else {
			header("Location: mission_report/newSchoolPrint.php?user=$user_id&school=$school_id&grade=$class_id&start=$start_date&end=$end_date&showDate=$showDate&dblSided=$dblSided");
		}
		*/
		$location = "Location: mission_report/newSchoolPrint.dev.php?user=$user_id&school=$school_id&grade=".implode(":", $class_id)."&start=$start_date&end=$end_date&showDate=$showDate&dblSided=$dblSided";
		header($location);
		exit;
	}
	/***************** GENERATE THE DROPDOWNS **********************/
    get_classes_select($school_id, $class_id); // function defined on line 141
    //get_users_select($school_id, $class_id, $user_id); // function defined on line 179
	
} else if ($school_id){
    get_classes_select($school_id, $class_id); // function defined on line 141
    //get_users_select($school_id, $class_id, $user_id); // function defined on line 179
}

/***************** GENERATE THE USERS DROPDOWN **********************/
function get_users_select($school_id, $class_id, $user_id) {
    global $users_select; // use global to modify the $users_select object
    
    $sql = "SELECT u.user_id, u.first, u.last, u.class_id, c.class_grade, c.class_sub "; // select these items
    $sql = $sql . "FROM users AS u "; // from the users table
    $sql = $sql . "JOIN classes AS c USING (class_id) "; // get the class info
    $sql = $sql . "WHERE u.school_id=" . $school_id . " and u.user_registered > 0 "; // only registered users in that school
    if ($class_id > 0)
        $sql = $sql . "AND class_id=" . $class_id . " "; // if a class_id was provided then make sure that it is only users from that class
    $sql = $sql . "ORDER BY c.class_grade, c.class_sub, u.last, u.first"; // order it by the class, last and first name
    //echo $sql;
    $query = mysql_query($sql); // run the query
    // generate the dropdown
    $users_select = "<div class='user_list select_box'>"; // div for user_list
    $users_select = $users_select . "<a class='prev button'>"; // previous button
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Previous Student</span>"; // add the icon
    $users_select = $users_select . "</a>"; // end the "previous" button
    $users_select = $users_select . "<select name='user_id' id='user_id' class='sSelect'>"; // create the dropdown (using sSelect)
    $users_select = $users_select . "<option value='-1'>All students</option>"; // default All studnets option (id -1)
    // for each user in the query result
    while ($row = mysql_fetch_assoc($query)) {
        $grade = $row['class_grade'] . ($row['class_sub'] ? "-" . $row['class_sub'] : ""); // get the grade + subject
        // if the user id provided equals the user_id of the loaded row
        if ($user_id == $row['user_id']) // add a option that is marked as selected
            $users_select = $users_select . "<option selected value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";
        else // otherwise just generate the row
            $users_select = $users_select . "<option value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";       
    }
    // finish the select
    $users_select = $users_select . "</select>";
    $users_select = $users_select . "<a class='next button'>"; // add the "Next Student" button
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Next Student</span>";
    $users_select = $users_select . "</a>";
    $users_select = $users_select . "</div>"; // close the div from line 154
}
/***************** GENERATE THE CLASSES DROPDOWN **********************/
function get_classes_select($school_id, $class_id) {
    global $classes_select; // use the global $classes_select so that it is available in the global context
    // get all the classes from the school
    $sql = "SELECT * FROM classes WHERE school_id=" . $school_id . " and class_era = 0 order by class_grade, class_sub";
    $query = mysql_query($sql); // run the query
    // generate the "Previous Platoon" button
    $classes_select = "<div class='class_list select_box'>";
    $classes_select = $classes_select . "<select name='class_id[]' multiple id='class_id'>"; // generate the select dropdown
    $classes_select = $classes_select . "<option value='-1' selected disabled>Entire School</option>";
    // get all the classes
    while ($row = mysql_fetch_assoc($query)) {      
        if ($class_id == $row['class_id']) // if the class_id provided is the current one mark it as selected
            $classes_select = $classes_select . "<option selected value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
        else // otherwise just generate the default option tag
            $classes_select = $classes_select . "<option value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
    }
    // create the "Next Platoon" button....
    $classes_select = $classes_select . "</select>"; // end the select
    $classes_select = $classes_select . "</div>"; // end the div from line 183
}


// ***** SCHOOLS ***** //
if ($admin->auth == "super") {
    $schools_sql = "SELECT school_id, school_name FROM schools where school_era is null ORDER BY school_name";
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
    $sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' and start_date > $start ORDER BY start_date";    
    $query = mysql_query($sql);
    while ($row = mysql_fetch_assoc($query)) {
        $report = new report($row);
        //hide pesach 2014
        if ($report->start_date == 2456761 || $report->start_date == 2456768) continue;
		//if ($report->start_date >= 2456927 && $admin_user['auth'] != 'super') continue;
        array_push($reports, $report);
    }
    // ***** REPORT DATES ***** //
}

$school_id_temp = $school_id;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Print Mission Sheets</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
		<link href="/styles/utils/custom_multi_dropdown.css" rel="stylesheet">
		<link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <style>
        div#school_list {width: 100%;height: 40px;}
		.class_list.select_box {margin: 0px 25px;}
/*		.easyselect-box-item, .easyselect-field{border-color: #D3D3D3 #AAAAAA #888888; border-width: 1px; background: url("/images/bg_smallButton.png") repeat-x scroll 0 0 #D1D1D1;}*/
/*		.easyselect-field{padding: 6px;}	a.easyselect-box-item{color: #000; font-weight: normal;}*/
        /*div#class_list_div {float: left;width: 50%;}*/
		div#right-side {width: 50%;float: right;}
		div#left-side {float: left;width: 50%;}
/*		.easyselect-box{background: none;}*/
		center{display: inline-block; clear: both; margin-top: 15px; margin-bottom: 15px; width: 100%;font-size:13.33px;padding:3px 7px;font-weight:normal;}
		
/*		Override dropdown styles for consistent look*/
		.multiDropdown-main{min-height: 250px;font-family:"Trebuchet MS",Arial,Helvetica,sans-serif;}
		.multiDropdown-main a:hover{
			background-position: bottom;
			border-color: #888888 #AAAAAA #D3D3D3;
		}
		.multiDropdown-options ul{
			height: auto;
			max-height: 200px;
		}
		#printModal{
			text-align: center;
			display: inline-block;
			width: 100%;
			padding: 10px;
			color: #000;
		}
		</style>
		
    </head>

    <body>
        <? include('admin_header.php'); // load up the admin UI. This sets $school_id to -1 ?>
        <? if ($school_id == -1 && $school_id_temp) $school_id = $school_id_temp; // update the school id...?>
        
        <div class="body left marking_missions">
            <h1>Print Mission Sheets</h1>
            <? // create the form using 'PHP_SELF' so that it redirects to this page regardless of the file name ?>
            <form name="date_tasks_report" id="date_tasks_report" action="<?=$_SERVER['PHP_SELF']?>" method="post" accept-charset="UTF-8">
            <? // make sure that there is a school id before we generate the report... ?>
            <? if ($school_id > 0) { ?>
                <div class"module noprint">
                    <!--<a href="date_tasks_print.php">Take me back to the OLD STYLE mission sheets for printing!</a><br />-->
                    Click <a href="settings.php?admin_id=<?=$admin->admin_id?>&school_id=<?=$school_id?>">here</a> to change mission settings</a>
                </div>
            
                <div class="noprint infobox">
                    <strong>PLEASE NOTE:</strong> NEW printing instructions! (see below)<br />
                    Optimized for <em>Mozilla Firefox</em><br />
                    Please do a test printing on the computer you will use for printing missions throughout the year. Thank you.
                </div>
            
                <div class="noprint">
                    <div class="module clearfix" style="clear:both;">
						<a id="printModal" onclick="window.open('/mission_report/instructions/', '_blank', 'width=770, height=700, menubar=no, scrollbars=yes, status=no, toolbar=no, titlebar=no')">
							Updated Printing Instructions
						</a>
                    </div>
                    
                    <div class="module clearfix generate">
                        <p>Generate Mission Sheets by choosing an option from all the fields below.</p>
                        
                        <p><strong>Please indicate whether you are printing double sided copies or not.</strong></p>
                        
                        <? $dblSided = isset($_POST['dblSided']) ? $_POST['dblSided'] : 1; ?>
                        <p>
                            <input type="radio" name="dblSided" value="1" <? if ($dblSided == 1) echo 'checked=\"checked\"' ?> /> I AM printing double sided copies<br />
                            <input type="radio" name="dblSided" value="0" <? if ($dblSided == 0) echo 'checked=\"checked\"' ?> /> I am NOT printing double sided copies.
                        </p>
                        
                        <br />
                        <p><strong>Please indicate whether you want to show the dates on your mission sheets, <br />
                            and whether it should be in Hebrew or in English.</strong>
                        </p>
                        
                        <? $showDate = isset($_POST['showDate']) ? $_POST['showDate'] : 1; ?>
                        <p>
                            <input type="radio" name="showDate" value="0" <? if ($showDate == 0) echo 'checked=\"checked\"' ?> /> Do NOT show dates<br />
                            <input type="radio" name="showDate" value="1" <? if ($showDate == 1) echo 'checked=\"checked\"' ?> /> Show Hebrew dates<br />
                            <input type="radio" name="showDate" value="2" <? if ($showDate == 2) echo 'checked=\"checked\"' ?> /> Show Hebrew & English dates<br />
                        </p>
                    </div>
                </div>
            <? } // end if school_id is set ?> 
                <input type="hidden" name="action" id="action" value="">
                
                <div class="infobox2 marking_list clearfix noprint">
                    <?// school select dropdown ?>
                    
					<div id="left-side">
						<div id="school_list">
							<div class="school_list select_box">
								<a class="prev button">
									<span class="icon"></span>
									<span class="label"><?=T_('Previous School')?></span>
								</a>
								<select name="school_id" id="school_id">
									<? if(!$school_id) echo "<option value='-1'>Please select a school</option>" ?>
									<? while ($school = mysql_fetch_assoc($schools_query)){ ?>
										<option <?= $school_id == $school['school_id'] ? "selected" : ""; ?> value="<?=$school['school_id'];?>"><?=$school['school_name'];?></option>
									<?} // end the while loop for the schools ?>
								</select>
								<a class="next button">
									<span class="icon"></span>
									<span class="label"><?=T_('Next School')?></span>
								</a>                        
							</div>
						</div>
					
						<div id="class_list_div" name="class_list_div">
							<?=$classes_select;?>
						</div>
					</div>
					
					<div id="right-side">
						<!--<div id="user_list_div" name="user_list_div">
							<?=$users_select;?>
						</div>-->
						
						<!-- ***** WEEKLY PERIOD ***** -->
						<? if ($school_id > 0) { // make sure that the school id is set  ?>
						<div class="date_list select_box">                  
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous Week')?></span>
							</a>
							<?$jd = unixtojd();?>
							<select name="start_date_list" class="sSelect">
								<? for ($rno = 0; $rno < count($reports); $rno++) {?>
									<? $report = $reports[$rno]; ?>
									<option value="<?=$report->start_date;?>"
										<?=($start_date > $report->start_date) || ($jd >= ($report->start_date-6) && $jd <= ($report->end_date-6)) ? "selected": "";?>>
										From <?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?>
									</option>
								<? } ?>
							</select>
							
							<a class="next button">
								<span class="icon"></span><span class="label"><?=T_('Next Week')?></span>
							</a>
						</div>
						<div class="date_list select_box">                  
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous Week')?></span>
							</a>
							
							<select name="end_date_list" class="sSelect">
								<? for ($rno = 0; $rno < count($reports); $rno++) {?>
									<? $report = $reports[$rno];?>
									<option value="<?=$report->end_date;?>"
										<?=($end_date == $report->end_date) || ($jd >= ($report->start_date-6) && $jd <= ($report->end_date-6)) ? "selected": "";?>>
										To <?=$report->report_name;?> - <?=jdtogregorian($report->end_date);?>
									</option>
								<? } ?>
							</select>
							
							<a class="next button">
								<span class="icon"></span><span class="label"><?=T_('Next Week')?></span>
							</a>
						</div>
						<? } ?>
						<!-- ***** WEEKLY PERIOD ***** -->
					</div>
					<? if ($school_id > 0) { ?>
					<center>
						<input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='produce_report';return doAlert();">
					</center>
					<? } ?>
                </div>
            </form>
		</div>
<script src="/js/utils/custom_multi_dropdown.js" type="text/javascript"></script>
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
        });
        // ***** SCHOOL LIST CHANGE ***** //
        
        // ***** CLASS LIST CHANGE ***** //
        //$(".class_list select").sSelect().change(function () {
        //    document.getElementById('action').value = "get_selects";
        //    $('#date_tasks_report').submit();
        //});
		//$(".class_list select").easySelect();
		//$(".easyselect-field").attr("placeholder", "Type your class here");
		$("#class_id").multiDropdown("Entire School");
        // ***** CLASS LIST CHANGE ***** //
        
        // ***** USER LIST CHANGE ***** //
        $(".user_list select").sSelect().change(function () {
            //if (number_of_students > 0)
            //  $(this).closest('form').submit();
        });
        // ***** USER LIST CHANGE ***** //
        
        // ***** WEEKLY PERIOD CHANGE ***** //
        $(".date_list select").sSelect().change(function () {
            if ($("select[name='start_date_list']").val() > $("select[name='end_date_list']").val()) {
                alert("Please make sure that your TO date is AFTER your FROM date or you will get blank mission sheets");
            }
        });
        // ***** WEEKLY PERIOD CHANGE ***** //
                        
        $(".marking_list #display_submit").hide();
        
        $('.slider:last .list_expand li h3').nextAll().hide();
        $('.slider:last .list_expand li h3').click(function(){
            $(this).nextAll().slideToggle('fast');
            $(this).parents('li').toggleClass('open');
        });               
        
    });

function doAlert() {
	/*
	var val = $("#class_list_div select").val();
	if (isNaN(val)) {
		alert("You must choose a platoon.");
		return false;
	} else {
	*/
    	alert('Important Notice: Please be advised that the page may take a long time to load. Please be patient and wait until it is completely loaded.');
    	//alert('Important Notice: Please be advised that the page may take a long time to load.\nDO NOT PANIC!\nJust WAIT until the browser is finished loading.\nIf an error message pops up just click on \'do not ask me again\' and then click \'continue\'.\n If the browser says \'not responding\' JUST WAIT!');
    	return true;
    //}
}
</script>

</body>
</html>
                