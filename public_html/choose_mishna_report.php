<?
$admin_auth = array('school');
require_once 'header.php';

if (isset($_POST['submit'])) {
	$reportType = $_POST['reportType'];
	switch ($reportType) {
		case 1:
			header("Location: mishna_report.php?id=" . $_POST['school']);
			break;
		case 2:
			if ($_POST['grade'] > 0) {
				header("Location: mishna_platoon_report.php?id=" . $_POST['grade']);
			} else {
				header("Location: mishna_platoon_report.php?id=" . $_POST['school'] . "&idType=school");
			}
			break;
		case 3:
			if ($_POST['student'] > 0 || $_POST['user'] > 0) {
				$user = ($_POST['user'] ? $_POST['user'] : $_POST['student']);
				header("Location: mishna_user_report.php?id=" . $user);
			} else if ($_POST['grade'] > 0) {
				header("Location: mishna_user_report.php?id=" . $_POST['grade'] . "&idType=grade");
			} else {
				header("Location: mishna_user_report.php?id=" . $_POST['school'] . "&idType=school");
			}
			break;
	}
	exit;
}

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

require_once 'class.schoolClasses.php';
foreach ($schools as $id => $school) {
	$s = new SchoolClasses( $id );
	break;
}
$grades = $s->getClasses();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Assign Mishnayos</title>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.8.24/themes/base/jquery-ui.css">
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
        	fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .middle {
            	text-align: center;
            	margin: auto;
            }
        </style>
 		
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1>Choose Mishna Report</h1>
        
        <form action="choose_mishna_report.php" method="post">
        	<div id="reportType">
	        	<fieldset>
	        		<legend>Report Type</legend>
	        		<input type="radio" name="reportType" value="1" checked="checked" /> Base Report<br />
	        		<input type="radio" name="reportType" value="2" /> Platoon Report<br />
					<input type="radio" name="reportType" value="3" /> Soldier Report
	        	</fieldset>
			</div>
			
			<div id="choice">
				<br />		
		        <fieldset>
		        	<legend>Choose Grades / Students</legend>
					<?
					$numSchools = count($schools);
					if ($numSchools == 1) {
						foreach ($schools as $schoolID => $school) {
							echo "<input type='hidden' id='school' name='school' value='" . $schoolID . "' />";
						}
					} else {
						echo "<select name='school' id='school'>";
						foreach ($schools as $schoolID => $school) {
							echo "<option value='" . $schoolID . "'>" . $school . "</option>";
						}
						echo "</select><br />";
					}
					?>
					<select name="grade" id="grade">
						<option value="-1">All Grades</option>
						<?
						foreach ($grades as $grade) {
							$desc = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
							echo "<option value='" . $grade['class_id'] . "'>" . $desc . "</option>";
						}
						?>
					</select>
					<br />
					
					<select name="student" id="student">
						<option value="-1">All Students</option>
					</select>
					<div id="chooseUser">
						<br />
						OR
						<br />
						Find a student by starting to type in name:<br />
						<input id="users" />
						<input type="hidden" id="user" name="user" />
					</div>
		        </fieldset>
		    </div>
	        
	        <br />
	        <input type="submit" name="submit" value="Submit" id="submit" />
		</form>
	</body>
	
	<script src="//code.jquery.com/jquery-1.8.2.js"></script>
	<script src="//code.jquery.com/ui/1.8.24/jquery-ui.js"></script>
	<script>
		$("#reportType input").click( function() {
			var val = $(this).val();
			if (val == 1) {
				$("#choice").hide();
			} else {
				if (val == 2) {
					$("#student").hide();
					$("#chooseUser").hide();
				} else {
					$("#student").show();
					$("#chooseUser").show();
				}
				$("#choice").show();
			}
		});
	
		$("#school").change( function() {
			var val = $(this).val();
			$.get('ajax/getClasses.php', {id : val, hasUsers : 1}, function( reply ) {
				var grades = $.parseJSON( reply );
				$("#grade").empty();
				$("#student").empty();
				var str = "<option value='-1'>All Grades</option>";
				for (var g in grades) {
					str += "<option value='" + g + "'>" + grades[g] + "</option>";
				}
				$("#grade").append( str );
				$("#student").append("<option value='-1'>All Students</option>");
			});
		});
		
		$("#grade").change( function() {
			var val = $(this).val();
			$.get('ajax/getUsers.php', {id : val}, function( reply ) {
				var students = $.parseJSON( reply );
				$("#student").empty();
				var str = "<option value='-1'>All Students</option>";
				for (var s in students) {
					str += "<option value='" + s + "'>" + students[s] + "</option>";
				}
				$("#student").append( str );
			});
		});
		
		$(function() {
			$("#choice").hide();
			/*
			$.post('ajax/getStudents.php', {school : $("#school").val()}, function( success ) {
				var students = $.parseJSON( success );
				var str = "";
				for (s in students) {
					str += "<option value='" + s + "'>" + students[s] + "</option>";
				}
				$("#user").append(str);
			});
			*/
			$.post('ajax/getStudents.php', { school : $("#school").val() }, function( success ) {
				var users = $.parseJSON( success );
				var students = [];
				for (var u in users) {
					students.push({
						label : users[u], 
						value : u
					});
				}
				$("#users").autocomplete({
					source : students, 
					select : function(e, ui) {
						$("#users").val( ui.item.label );
						$("#user_id").val( ui.item.value );
						return false;
					}
				});
			});
		});
	</script>