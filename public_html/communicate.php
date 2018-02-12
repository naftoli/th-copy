<?
$admin_auth = array('school'); 
require('header.php'); 

// PROGRAM DIRECTOR //
$sql = "SELECT title, first, last, admin_email FROM admins where admin_id = " . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin_name = strtoupper($row['title'] . ' ' . $row['first'] . ' ' . $row['last']);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="communicate.css" rel="stylesheet" type="text/css">
		<title>Communicate with Parents</title>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Communicate with Parents</h1>
		
		<form action="communicate2.php" method="post">
			<div id="type">
				<fieldset>
					<legend>Type of Communication</legend>
					<input type="radio" name="type" class="type" value="missions" /> Email MISSION REPORT to parents<br />
					<input type="radio" name="type" class="type" value="letter" /> Email or Print LETTER to parents
				</fieldset>
			</div>
			
			<div id="method">
				<br />
				<fieldset>
					<legend>Method of communication</legend>
					<input type="radio" name="method" class="method" value="email" checked="checked" /> Email<br />
					<input type="radio" name="method" class="method" value="print" /> Print
				</fieldset>
			</div>
			
			<div id="signature">
				<br />
				<fieldset>
		        	<legend>Choose Signature</legend>
		        	<input type="radio" name="signature" class="signature" value="1" /> Sign Letter by Base Commander 
		        	(<a href="#" id="editBC"><?=$admin_name?></a>)<br />
		        	<input type="radio" name="signature" class="signature" value="2" /> Sign Letter by Teacher 
		        	(<a href="#" id="editTeachers">view / edit teachers</a>)
		        </fieldset>
		    </div>
			
			<br />
			<input type="submit" name="submit" id="submit" value="continue" />		
		</form>
		
		<div id="dialog"></div>

		<script>
			$(function() {
				$("#method").hide();
				$("#signature").hide();
				$("#submit").hide();
				$("#dialog").hide();
				
				$(".type").click(function() {
					var val = $(this).val();
					if (val == 'missions') {
						$("#method").hide();
						$("#signature").show();
						$("#submit").hide();
					} else if (val == 'letter') {
						$("#method").show();
						$("#signature").show();
						$("#submit").hide();
					}
				});
				
				$(".method").click(function() {
					$("#signature").show();
					$("#submit").hide();
				});
				
				$(".signature").click(function() {
					$("#submit").show();
				});
				
				$("#editBC").click(function() {
					var win = window.open('admin_profile_modal.php', '_blank', 'height=270, width=300');
					win.onbeforeunload = function() {
						window.location = 'communicate.php';
					}					
				});
				
				$("#editTeachers").click(function() {
					var school = <?=$admin_user['auths']['school'][0]?>;
					var win = window.open('admin_teachers_modal.php?id=' + school, '_blank', 'height=470, width=300');
					win.onbeforeunload = function() {
						window.location = 'communicate.php';
					}					
				});
			});
		</script>
	</body>
</html>