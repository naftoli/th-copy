<?
if (isset($_POST['mission_id']))
	$mission_id = $_POST['mission_id'];
else
	$mission_id = 0;

$admin_auth = array('school');
require_once 'header.php';

require_once 'class.seferHamitzvos.php';
$seferHamitzvos = new seferHamitzvos();

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
if ($admin->auth != "super") {
	$admin->get_schools();
	if (count($admin->schools) == 1) {
		$school_id = $admin->schools[0]['school_id'];
	}
}

// ***** SCHOOLS ***** //
if ($admin->auth == "super"){
	$schools_sql = "SELECT school_id, school_name FROM schools ORDER BY school_name";
	$schools_query = mysql_query($schools_sql);
}
elseif (count($admin->schools) > 0) {
	$schools_sql = "SELECT s.school_id, s.school_name FROM schools AS s JOIN admin_auths AS aa ON (aa.admin_id=" . $admin->admin_id . " AND aa.auth='school' AND aa.id=s.school_id) ORDER BY school_name";
	$schools_query = mysql_query($schools_sql);
}
// ***** SCHOOLS ***** //

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
} else {
	$user_id = 0;
}

$classes_select = "";
$users_select = "";
$users = array();
$users_string = '';

if (isset($_POST['action'])){

	if (isset($_POST['class_id']))
		$class_id = $_POST['class_id'];
	else
		$class_id = null;
		
	if (isset($_POST['user_id']))
		$user_id = $_POST['user_id'];
	else
		$user_id = null;	
	 
	get_classes_select($_POST['school_id'], $class_id);
	get_users_select($_POST['school_id'], $class_id, $user_id);
	
}
else{
	$action = 'get_selects';
}

function get_classes_select($school_id, $class_id) {
	global $classes_select;
	
	$sql = "SELECT * ";
	$sql .= "FROM classes ";
	$sql .= "WHERE school_id=" . $school_id . " ";
	$sql .= "AND class_era=0 ";
	$sql .= "ORDER BY class_grade, class_sub";
	$query = mysql_query($sql);
	
	$classes_select = "<div class='class_list select_box'>";
	$classes_select = $classes_select . "<a class='prev button'>";
	$classes_select = $classes_select . "<span class='icon'></span>";
	$classes_select = $classes_select . "<span class='label'>Previous Platoon</span>";
	$classes_select = $classes_select . "</a>";
	$classes_select = $classes_select . "<select id='class_id' name='class_id'>";
	$classes_select = $classes_select . "<option value='0'>All platoons</option>";
	
	while ($row = mysql_fetch_assoc($query)) {		
		if (!is_null($class_id) && $class_id == $row['class_id']) 
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

function get_users_select($school_id, $class_id, $user_id) {
	global $users_select;
	global $users;
	global $users_string;
	
	$sql = "SELECT u.user_id, u.first, u.last, u.class_id, c.class_grade, c.class_sub ";
	$sql = $sql . "FROM users AS u ";
    $sql = $sql . "JOIN classes AS c USING (class_id) ";
    $sql = $sql . "JOIN user_tracks AS ut USING (user_id) ";    
	$sql = $sql . "WHERE u.school_id=" . $school_id . " and u.user_registered > 0 ";
	$sql = $sql . "AND ut.subject_id = 21 AND ut.enrolled = 1 ";
	if ($class_id > 0) 
		$sql = $sql . "AND class_id=" . $class_id . " ";
    if ($user_id > 0) 
        $sql .= "ORDER BY c.class_grade, c.class_sub, u.last, u.first";
    else 
	   $sql = $sql . "ORDER BY u.class_id, u.last, u.first";
	$query = mysql_query($sql);
	
	$users_select = "<div class='user_list select_box'>";
	$users_select = $users_select . "<a class='prev button'>";
	$users_select = $users_select . "<span class='icon'></span><span class='label'>Previous Student</span>";
	$users_select = $users_select . "</a>";
	$users_select = $users_select . "<select name='user_id' id='user_id' class='sSelect'>";
	$users_select = $users_select . "<option value='0'>All students</option>";
	
	while ($row = mysql_fetch_assoc($query)) {
	
		if (isset($_POST['user_id']) && $_POST['user_id'] > 0){
			if ($row['user_id'] == $_POST['user_id'])
				array_push($users, $row);
			$users_string = $row['user_id'] . ':';
		}
		else{
			$users_string .= $row['user_id'] . ':';
			array_push($users, $row);
		}
		
		$grade = $row['class_grade'];
		if ($row['class_sub'] != "")
			$grade = $grade . "-" . $row['class_sub'];
			
		if ($user_id == $row['user_id'])
			$users_select = $users_select . "<option selected value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";
		else
			$users_select = $users_select . "<option value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";		
	}
	
	$users_string = substr($users_string, 0, strlen($users_string) - 1);
	
	$users_select = $users_select . "</select>";
	$users_select = $users_select . "<a class='next button'>";
	$users_select = $users_select . "<span class='icon'></span><span class='label'>Next Student</span>";
	$users_select = $users_select . "</a>";
	$users_select = $users_select . "</div>";
}

$uno = -1;
?>

<html>
	
	<head>
		<title>Sefer Hamitzvos Missions</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			table, tr, th, td {
				border: 1px dashed grey;
				padding: 10px;
				text-align: center;
			}
			.left {
				float: left;
				width: 30%;
				position: relative;
			}
			.middle {
				margin-left: 35%;
				position: relative;
			}
			.right {
				float: right;
				width: 30%;
				position: relative;
			}
			
			.students_list{
				border: 1px solid grey;
				padding: 10px;
			}
			
			.students_list h1{
				color:blue;
				font-family:"Trebuchet MS",Arial,Helvetica,sans-serif;
				font-size:14px;
				font-weight:bold;
			}
			
			.students_list table{
				width:100%;
			}
			
			.spacer{
				height:20px;
			}
		</style>

	</head>
	
	<body>
		<? require_once 'admin_header.php';	?>
			
		<h1>Sefer Hamitzvos Campaign</h1>
		
        <div id="info">Please Note: Only children signed up to the Sefer Hamitzvos Campaign will show up.</div>   
        <br />
		
		<form name="sefer_hamitzvos" id="sefer_hamitzvos" action="sefer_hamitzvos.php" method="post">
			<input type="hidden" name="action" id="action" value="<?=$action;?>" />
			<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_user['admin_id'];?>" />
			<input type="hidden" name="users" id="users" value="<?=$users_string;?>" />
			
			<div class="infobox2 marking_list clearfix noprint">
			
				<!-- SCHOOLS -->
				<div class="school_list select_box">
					<a class="prev button">
						<span class="icon"></span>
						<span class="label"><?=T_('Previous School')?></span>
					</a>
					
					<select name="school_id" id="school_id">
						<OPTION value="0">Please select a school</OPTION>
						<? while ($school = mysql_fetch_assoc($schools_query)) : ?>
						
							<? if ($school_id == $school['school_id']) : ?>
								<OPTION selected value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
							<? else : ?>
								<OPTION value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
							<? endif; ?>
						
						<? endwhile; ?>
					</select>
						
						
					<a class="next button">
						<span class="icon"></span>
						<span class="label"><?=T_('Next School')?></span>
					</a>						
				</div>
				<!-- SCHOOLS -->
				
				<? if (isset($_POST['action'])) : ?>
				<!-- CLASSES -->
				<div class="class_list" style="float:right;">
					<?=$classes_select;?>
				</div>
				<!-- CLASSES -->
				
				<!-- STUDENTS -->
				<div class="student_list">
					<?=$users_select;?>
				</div>
				<!-- STUDENTS -->
				
				
				<!-- MISSIONS -->
				<div class="mission_list select_box">
					<a class="prev button">
						<span class="icon"></span>
					</a>
					
					<select name="mission_id" id="mission_id">
						<option value="0">All missions</option>
						<? foreach ($seferHamitzvos->mitzvos as $key => $value) : ?>
						<option <? if ($key == $mission_id || $key == 1) echo ' selected="selected" '; ?> value="<?=$key;?>"><?='(' . $key . ') ' . $value;?></option>
						<? endforeach?>
					</select>
					
					<a class="next button">
						<span class="icon"></span>
					</a>					
				</div>
				<!-- MISSIONS -->		
				<? endif; ?>
				
				<br />
				<br />
				
				<? if (isset($_POST['action'])) : ?>
				<center>
					<span>
						<input type="checkbox" id="checkall" />Check All
					</span>
					<span>
						<input type="checkbox" id="uncheckall" />Uncheck All
					</span>					
				</center>
				<? endif; ?>
				
			</div>
			
			
			<?php if (count($users) > 0) : ?>
			
			<div class="students">
			
				<?php if ($mission_id == 0) : ?>
				
					<?php foreach ($users as $user) : $uno++; ?>
						<div class="students_list" data="<?=$user['user_id'];?>" id="student_<?=$user['user_id'];?>">
							<div class="spacer"></div>
							
							<h1>
								<?=$user['class_grade'] . '-' . $user['class_sub'] . ' ' . 
								    $user['first'] . ' ' . $user['last'];?>
							</h1>
							
							<center>
								<span>
									<input type="checkbox" data="<?=$user['user_id'];?>" class="studentcheckall" />Check All
								</span>
								<span>
									<input type="checkbox" data="<?=$user['user_id'];?>" class="studentuncheckall" />Uncheck All
								</span>					
							</center>
							
							<div class="spacer"></div>
							
							<?php $seferHamitzvos->get_student_missions($user, $mission_id); ?>
						</div>
					<?php endforeach; ?>
					
				<?php else : ?>
				
					<table>
						<tr><th>Class</th><th>Student</th><th>Mission</th><th>Mitzvos</th><th>Done</th></tr>
						<?php foreach ($users as $user) : $uno++; ?>
							<?php $seferHamitzvos->get_student_missions($user, $mission_id); ?>
						<?php endforeach; ?>					
					</table>
					
				<?php endif; ?>
				
			</div>
			
			<?php endif; ?>
			
		</form>

		<script type="text/javascript">
			$(document).ready(function() {
			
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
				$(".school_list select").sSelect().change(function(){
					$('#sefer_hamitzvos').submit();
				})
				// ***** SCHOOL LIST CHANGE ***** //
				
				<? if (isset($_POST['action'])) : ?>
				// ***** CLASS LIST CHANGE ***** //
				$(".class_list select").sSelect().change(function(){
					$('#sefer_hamitzvos').submit();
				})
				// ***** CLASS LIST CHANGE ***** //
				
				// ***** STUDENT LIST CHANGE ***** //
				$(".student_list select").sSelect().change(function(){
					$('#sefer_hamitzvos').submit();
				})
				// ***** STUDENTLIST CHANGE ***** //
				
				// ***** USER LIST CHANGE ***** //
				$(".mission_list select").sSelect().change(function(){
					$('#sefer_hamitzvos').submit();
				})
				// ***** USER LIST CHANGE ***** //				
				<? endif; ?>
				
				$('#checkall').click(function(){
					$('#uncheckall').attr('checked', false);
					
					$.each($('.studentcheckall'), function(){$(this).attr('checked', true);});
					$.each($('.studentuncheckall'), function(){$(this).attr('checked', false);});
					
					if ($('#mission_id').val() == '0')
						all_missions(true);
					else
						one_mission(true);					
				});
				
				$('#uncheckall').click(function(){
					$('#checkall').attr('checked', false);
					
					$.each($('.studentcheckall'), function(){$(this).attr('checked', false);});
					$.each($('.studentuncheckall'), function(){$(this).attr('checked', true);});
					
					if ($('#mission_id').val() == '0')
						all_missions(false);
					else
						one_mission(false);					
				});
				
				function all_missions(checked){
					var url = 'json_functions.php?function=update_student_sefer_hamitzvos';
				
					var student_no = 0;
					var students = '';
					var missions = '';
					
					$.each($('.students_list'), function(){
						students += $(this).attr('data') + ':';
						
						$.each($(this).find('table').find('input'),function(){
							$(this).attr('checked', checked);						
							if (student_no == 0)
								missions += $(this).attr('data') + ':';
						});
						
						student_no++;
					});
					
					students = students.substr(0, students.length - 1);
					missions = missions.substr(0, missions.length - 1);
					
					url += '&users=' + students;
					url += '&missions=' + missions;
					url += '&done=' + checked;
					
					$.getJSON(url, function(){});
				}
				
				function one_mission(checked){
					var url = 'json_functions.php?function=update_student_sefer_hamitzvos&missions=' + $('#mission_id').val() + '&users=';
					$.each($('.students').find('input'), function(){
						$(this).attr('checked', checked);
						url += $(this).parent().attr('data') + ':';
					});
					url = url.substr(0, url.length - 1);
					url += '&done=' + checked;
					$.getJSON(url, function(){});				
				}
			
				$('.studentcheckall').click(function(){
					$(this).parent().parent().find('.studentuncheckall').attr('checked', false);
					student_missions($(this).attr('data'), true);
				});
				
				$('.studentuncheckall').click(function(){
					$(this).parent().parent().find('.studentcheckall').attr('checked', false);
					student_missions($(this).attr('data'), false);
				});
				
				function student_missions(user_id, checked){
					var url = 'json_functions.php?function=update_student_sefer_hamitzvos&users=' + user_id + '&missions=';
					$.each($('#student_' + user_id).find('table').find('input'), function(){
						$(this).attr('checked', checked);
						url += $(this).attr('data') + ':';
					});
					url = url.substr(0, url.length - 1);
					url += '&done=' + checked;
					$.getJSON(url, function(){});			
				}
			
				$('.studentcheckbox').click(function(){
					var url = 'json_functions.php?function=update_student_sefer_hamitzvos&users=' + $(this).parent().attr('data') + '&missions=' + $(this).attr('data') + '&done=' + $(this).attr('checked');
					$.getJSON(url, function(){});
				});

			});
		</script>
		
	</body>

</html>