<?php
$admin_auth = array('user');
$ui_type = 'child';
require('header.php');
include("check_admin_id.php");

include ("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$parent_name = "<i>" . $row['first'] ." " . $row['last'] . "</i>";
$admin = new \classes\admin($row);

if (isset($_POST['submit'])) {
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}
	
	require 'newClasses/newSoldier.php';
	$dob = $_POST['yy'] . '-' . $_POST['mm'] . '-' . $_POST['dd'];
	$s = new NewSoldier($admin, $_POST['first'], $dob, $_POST['gender'], $_POST['school'], $_POST['grade']);
	if ($s->create()) {
		echo "created.";
	} else {
		echo "error.";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Registration - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<style>
			td {
				padding: 5px;
				font-size: 14px;
				vertical-align: middle;
			}
		</style>
	</head>

	<body>
		<? include("admin_header.php"); ?>
		
		<div class="body">

			<h1>Create New Child</h1>
		
			<NOSCRIPT>
				<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
			</NOSCRIPT>
			
			<div class="content">
				
				<form action="new_child.php" method="post">
					<table>
						<tr>
							<td>Choose School</td>
							<td>
								<select name="school" id="school">
									<option value='61'>MyShliach</option>
									<option value='269'>Anash Kinder</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>Choose Grade</td>
							<td>
								<select name="grade" id="grade">
								<?
								$grades = array();
								$sql = "select * from classes 
										where school_id = 61 
										and class_era = 0 
										order by class_grade";
								$result = mysql_query($sql);
								while ($row = mysql_fetch_assoc($result)) {
									$grades[] = $row;
								}
								foreach ($grades as $grade) {
									$id = $grade['class_id'];
									$name = $grade['class_grade'];
									if (!empty($grade['class_sub'])) $name .= "-" . $grade['class_sub'];
									echo "<option value='" . $id . "'>" . $name . "</option>";
								}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>First Name</td>
							<td><input type="text" name="first" id="first" /></td>
						</tr>
						<tr>
							<td>DOB</td>
							<td>
								<select name="mm">
									<option value='1'>Jan</option>
									<option value='2'>Feb</option>
									<option value='3'>Mar</option>
									<option value='4'>Apr</option>
									<option value='5'>May</option>
									<option value='6'>Jun</option>
									<option value='7'>Jul</option>
									<option value='8'>Aug</option>
									<option value='9'>Sep</option>
									<option value='10'>Oct</option>
									<option value='11'>Nov</option>
									<option value='12'>Dec</option>
								</select>
								<select name="dd">
									<?
									for ($i = 1; $i < 32; $i++) {
										echo "<option value='" . $i . "'>" . $i . "</option>";
									}
									?>
								</select>
								<select name="yy">
									<?
									//find out current year
									$yr = date('Y');
									//start is current yr - 14
									$startYr = $yr - 14;
									for ( ; $startYr < $yr; $startYr++) {
										echo "<option value='" . $startYr . "'>" . $startYr . "</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td style='vertical-align: top'>Gender</td>
							<td>
								<input type="radio" name='gender' value='m' class="gender" /> Boy<br />
								<input type="radio" name='gender' value='f' class="gender" /> Girl<br />
							</td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" name="submit" value="create" id="submit" /></td>
						</tr>
					</table>
				</form>

			</div>
			
		</div>

	</body>	
	<script>
		$(function() {
			$("#submit").click( function() {
				var first = $("#first").val();
				var gender = 0;
				$(".gender").each( function() {
					if ($(this).is(":checked")) {
						gender = $(this).val();
					}
				});
				
				if (first == '') {
					alert('You must enter a first name.');
					return false;
				}
				
				if (gender == 0) {
					alert('You must choose a gender.');
					return false;
				}
			});
		});
	</script>
</html>
