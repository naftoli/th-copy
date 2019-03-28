<?
session_start();
if (!isset($_SESSION['type']) || !isset($_SESSION['method']) || !isset($_SESSION['signature']) 
	|| !isset($_POST['submit'])) {
	header("Location: communicate.php");
}

$admin_auth = array('school'); 
require('header.php');

echo "<pre>";
//print_r($_SESSION);
//print_r($_POST);
echo "</pre>";

$ids = array();
foreach ($_POST as $k => $v) {
	if (is_int($k)) {
		$ids[] = $k;
	}
}

$_SESSION['choice'] = $_POST['choice'];
$_SESSION['school'] = $_POST['school'];
$_SESSION['ids'] = $ids;

if (isset($_POST['rank'])) {
	$_SESSION['rank'] = $_POST['rank'];
}

$schoolInfo = $_POST['school'];
$flag = strpos($schoolInfo, ':');
$schoolID = substr($schoolInfo, 0, $flag);
$schoolName = substr($schoolInfo, $flag+1);

// PROGRAM DIRECTOR //
$sql = "SELECT title, first, last, admin_email FROM admins where admin_id = " . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin_email = $row['admin_email'];

$signature = '';
$signed = $_SESSION['signature'];
if ($signed == 1) {
	$signature = $row["title"] . ' ' . $row["first"] . ' ' . $row["last"];
}
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
		<h1 class="no-print">Communicate with Parents</h1>
		
		<? if ($_SESSION['method'] == 'email') { ?>
		
			<form action="communicate5.php" method="post">
				<fieldset>
					<legend>Email properties</legend>
					<table>
						<tr>
							<td>Subject:</td>
							<td><input size="<?=strlen($schoolName)+20?>" type="text" name="emailSubject" value="Message from <?=$schoolName?>" /></td>
						</tr>
						<tr>
							<td>From:</td>
							<td><input size="30" type="text" name="from" id="from" value="<?=$admin_email?>" /> <i>must be a valid email address</i></td>
						</tr>
						<tr>
							<td>Reply to:</td>
							<td><input size="30" type="text" name="reply" id="reply" value="<?=$admin_email?>" /> <i>must be a valid email address</i></td>
						</tr>
					</table>
				</fieldset>
				<br /><br />
			
			<?
			if ($_SESSION['type'] == 'letter') {
				echo "The following email will be sent out to all selected child(ren) that have email addresses in their profile:<br /><br /><br />";
			} else if ($_SESSION['type'] == 'missions') {	
				echo "The mission report will be sent out to all selected child(ren) that have email addresses in their profile preceded by the following comment:<br /><br /><br />";
			}
			
			echo "<div class='letter'>Dear Parents of [student name],<br /><br />";
    		echo nl2br($_SESSION['content']) . "<br /><br />";
    		echo "[MISSION REPORT]<br /><br />";
			echo "Sincerely,<br />";
			if ($signed == 1) {
				echo $signature;
			} else if ($signed == 2) {
				echo "[teacher name]";
			}
			echo "<br /><br /><input type='submit' name='submit' id='email' value='Send Email' /></form>";
		 
		} else if ($_SESSION['method'] == 'print') {
			
			switch ($_POST['choice']) {
				case 1:
					$sql = "select * from users where school_id = " . $schoolID . " and user_registered > 0";
					break;
				case 2:
				case 3:
					$sql = "select * from users where {$choice}_id in(" . implode(',', $ids) . ")";
					break;
				case 4:
					$sql = "select u.* from users u 
							join rank_marks rm using (user_id) 
							where u.user_registered > 0 
							and rm.rank_ord = " . $_POST['rank']; 
					break;
			}
			
			require_once 'classes/user.php';
			$users = array();
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$user = new user($row);
				$user->get_school();
				$user->get_school_class();
				$users[] = $user;
			}
			
			echo "<div class='no-print' align='center'>";
			echo "<input type='button' value='Print' onclick='window.print()' />";
			echo "</div>"; 
		
			foreach ($users as $user) {
        		echo "Dear Parents of " . $user->first . ' ' . $user->last . ",<br /><br />";
				echo nl2br($_SESSION['content']) . "<br /><br />";
				echo "Sincerely,<br />";
				if ($signed == 1) {
					echo $signature;
				} else if ($signed == 2) {
					echo $user->school_class->class_teacher;
				}
				echo "<br /><br /><div class='page-break'></div>";
        	}
		}		
		?>
		
		<script>
        	$(function() {
        		$("#email").click(function() {
        			var from = $("#from").val();
        			var reply = $("#reply").val();
        			
        			if (from) {
        				if (!checkEmail(from)) {
        					$("#from").select();
        					return false;
        				}
        			}
        			
        			if (reply) {
        				if (!checkEmail(reply)) {
        					$("#reply").select();
        					return false;
        				}
        			}
        		});
        		
        		$("#from").blur(function() {
        			var val = $(this).val();
        			if (val) {
	        			if (!checkEmail(val)) {
	        				$(this).select();
	        			}
	        		}
        		});
        		
        		$("#reply").blur(function() {
        			var val = $(this).val();
        			if (val) {
	        			if (!checkEmail(val)) {
	        				$(this).select();
	        			}
	        		}
        		});
        		
        		function checkEmail(val) {
        			var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        			if (regex.test(val)) 
        				return true;
        			else {
        				alert("Invalid email please try again.");
        				return false;
        			}
        		}
        	});
        </script>
	</body>
</html>