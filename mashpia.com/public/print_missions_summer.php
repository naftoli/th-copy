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
    
    $school_id = $_POST['school_id'];   
    
    if (isset($_POST['class_id'])) 
        $class_id = $_POST['class_id'];
    else
        $class_id = 0;
            
    if (isset($_POST['user_id'])) 
        $user_id = $_POST['user_id'];
    else
        $user_id = 0;
            
    get_classes_select($school_id, $class_id);
    get_users_select($school_id, $class_id, $user_id);

    if ($action == "produce_report") {
    	$showDate = $_POST['showDate'];
		$dblSided = $_POST['dblSided'];
		header("Location: mission_report/newSchoolPrintSummer.php?user=$user_id&school=$school_id&grade=$class_id&showDate=$showDate&dblSided=$dblSided");
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
    $classes_select = $classes_select . "<select name='class_id' id='class_id'>";
    $classes_select = $classes_select . "<option value='-1'>Entire School</option>";
    
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
    $schools_sql = "SELECT school_id, school_name FROM schools where school_era is null ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
elseif (count($admin->schools) > 0) 
{
    $schools_sql = "SELECT s.school_id, s.school_name FROM schools AS s JOIN admin_auths AS aa ON (aa.admin_id=" . $admin->admin_id . " AND aa.auth='school' AND aa.id=s.school_id) ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
// ***** SCHOOLS ***** //

$sql = "select `val` from global_settings where `key` = 'current_year'";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$year = $row['val'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Print Summer Missions</title>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    </head>

    <body>
            
        <? include('admin_header.php'); ?>
        
        <div class="body left marking_missions">
                        
            <H1>Print Mission Sheets</H1>
            
            <form name="date_tasks_report" id="date_tasks_report" action="print_missions_summer.php" method="post" accept-charset="UTF-8">
            
            <? if ($school_id > 0) : ?>
				<div class"module noprint">
					<a href="date_tasks_print.php">Take me back to the OLD STYLE mission sheets for printing!</a><br />
					Click <a href="settings.php?admin_id=<?=$admin->admin_id?>&school_id=<?=$school_id?>">here</a> to change mission settings</a>
				</div>
				
	            <div class="noprint">

	            	<div class="infobox noprint">
		                Summer missions include 11 weeks worth of missions. Korach until Ki Seitzei.<br />
		                You can only print for each child individually.<br />
		            </div>
					
					<div class="module clearfix" style="clear:both;">
		                <div class="list_expand">
	                        <ul>
	                            <li>
	                                <h3><span class="icon"></span>Print Instructions</h3>
										<!--<img src="images/setup1.gif" width="325px;" align="left" />
	                                	<img src="images/setup2.gif" width="325px;" align="right" />
	                                	<div style="clear:both;"></div>
	                                	<br />-->
	                                	<p>In your browser click 'File' then 'Page Setup...'</p>
	                                    <p>Step 1: Set the Orientation to Portrait</p>
	                                    <p>Step 2: Set Scale to 90%</p>
	                                    <p>Step 3: In the second tab set Margins:<br />
	                                    	<div style="margin-left: 30px;">
						                		Top: 0.3<br />
						                		Left: 0.3<br />
						                		Right: 0.0<br />
						                		Bottom: 0.0<br />
						                	</div>
	                                    </p>
	                                    <p>Step 4: Set all Headers & Footers to Blank</p>
	                                    <p>Note: The browser will save these preferences for later use.</p>
	                            </li>
	                        </ul>
	                    </div>
	                </div>
            
	                <div class="module clearfix generate">
	                    <p>Generate Mission Sheets by choosing an option from all the fields below.</p>
	                    
	                    <p>Please indicate whether you are printing double sided copies or not.</p>
	                    
	                    <? $dblSided = isset($_POST['dblSided']) ? $_POST['dblSided'] : 1; ?>
	                    <p>
	                    	<input type="radio" name="dblSided" value="1" <? if ($dblSided == 1) echo 'checked=\"checked\"' ?> /> I AM printing double sided copies<br />
	                    	<input type="radio" name="dblSided" value="0" <? if ($dblSided == 0) echo 'checked=\"checked\"' ?> /> I am NOT printing double sided copies.
	                    </p>
	                    
	                    <br />
	                    <p>Please indicate whether you want to show the dates on your mission sheets, <br />
	                    	and whether it should be in Hebrew or in English.
	                    </p>
	                    
	                    <? $showDate = isset($_POST['showDate']) ? $_POST['showDate'] : 1; ?>
	                    <p>
	                    	<input type="radio" name="showDate" value="0" <? if ($showDate == 0) echo 'checked=\"checked\"' ?> /> Do NOT show dates<br />
	                    	<input type="radio" name="showDate" value="1" <? if ($showDate == 1) echo 'checked=\"checked\"' ?> /> Show Hebrew dates<br />
	                    	<input type="radio" name="showDate" value="2" <? if ($showDate == 2) echo 'checked=\"checked\"' ?> /> Show Hebrew & English dates<br />	                    	
	                    </p>
	                   
	                </div>
	            </div>
            <? endif; ?> 
            
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
                            <option value="1">Summer <?=$year?></option>
                        </select>
                        
                        <a class="next button">
                            <span class="icon"></span><span class="label"><?=T_('Next Week')?></span>
                        </a>
                    </div>
                    <? endif; ?>
                    <!-- ***** WEEKLY PERIOD ***** -->
                    
                    <? if ($school_id > 0) : ?>
                    <center>
                        <input class="submit" type="submit" value="GO" onclick="document.getElementById('action').value='produce_report';return doAlert();">                   
                    </center>                   
                    <? endif; ?>
                </div>
                
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
        
        $(".submit").click( function(e) {
        	e.stopPropagation();
        	e.preventDefault();
        	if ($("#user_id").val() == -1) {
        		alert('You must choose a class and student.');
        		return false;
        	} else {
        		$("#date_tasks_report").submit();
        	}
        });      
    });

function doAlert() {
	alert('Important Notice: Please be advised that the page may take a long time to load. Please be patient and wait until it is completely loaded.');
	return true;
}
</script>
        	
</body>
</html>