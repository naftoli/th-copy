<?
session_start();

if (!isset($_SESSION['school']) || !isset($_SESSION['schoolName']) || !isset($_SESSION['choice']) || 
	!isset($_SESSION['signature']) || !isset($_SESSION['admin_id'])) {
	header("Location: parent_letter.php");
}

if (!isset($_POST['method'])) {
	header("Location: parent_letter2.php");
}
		
$admin_auth = array('school'); 
require('header.php');

// PROGRAM DIRECTOR //
$sql = "SELECT title, first, last, admin_email FROM admins where admin_id = " . $_SESSION['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin_email = $row['admin_email'];

$signature = '';
$signed = $_SESSION['signature'];
if ($signed == 'bc') {
	$signature = $row["title"] . ' ' . $row["first"] . ' ' . $row["last"];
}

require_once 'classes/user.php';
$users = array();
$choice = $_SESSION['choice'];
if ($choice == 'school') {
	$sql = "select * from users where school_id = " . $_SESSION['school'] . " and user_registered > 0";
} else {
	$sql = "select * from users where {$choice}_id in(" . $_SESSION['ids'] . ")";
}
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$user = new user($row);
	$user->get_school();
	$user->get_school_class();
	$users[] = $user;
}

$msg = '';
if (isset($_POST['action'])) {
	
	if ($_POST['action'] == 'missions') {
	
		$start = $_POST['start'];
		$end = $_POST['end'];
		$userIDs = array();
		$userInfo = array();
		foreach ($users as $user) {
			$userIDs[] = $user->user_id;
			$userInfo[$user->user_id] = (array)$user;
		}
		require_once 'class.personalizedReport.php';
		$p = new PersonalizedReport($start, $end, $userIDs);
		
		$emailSubject = trim($_POST['emailSubject']);
		if (empty($emailSubject)) {
			$emailSubject = "Message from your child's school";
		}
		
		$from = trim($_POST['from']);
		if (empty($from)) {
			$from = 'school@mashpia.com';
		}
		
		$reply = trim($_POST['reply']);
		if (empty($reply)) {
			$reply = $from;
		}
		
		$props = array(
			'signed'	=>	$signed,
			'signature'	=>	$signature, 
			'emailSubject'	=>	$emailSubject, 
			'from'		=>	$from, 
			'reply'		=>	$reply
		);
		
		$p->setEmailProps($props);
		$msg = $p->createReport($userInfo, true);
		
	} else if ($_POST['action'] == 'letter') {
	
		if (isset($_POST['email'])) {
			
			//prepare email
			$emailSubject = trim($_POST['emailSubject']);
			if (empty($emailSubject)) {
				$emailSubject = "Message from your child's school";
			}
			
			$from = trim($_POST['from']);
			if (empty($from)) {
				$from = 'school@mashpia.com';
			}
			
			$reply = trim($_POST['reply']);
			if (empty($reply)) {
				$reply = $from;
			}
			
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$headers .= 'From: ' . $from . "\r\n";
			$headers .= 'Reply-To: ' . $reply . "\r\n";
				
			foreach ($users as $user) {
				//$to = 'naftolir@gmail.com'; 
				$to = $user->email;
				
				if (!empty($to)) {
					$body = "Dear Parents of " . $user->first . ' ' . $user->last . ",<br /><br />";
					$body .= nl2br($_POST['content']) . "<br /><br />";
					$body .= "Sincerely,<br />";
					if ($signed == 'bc') {
						$body .= $signature;
					} else if ($signed == 'teacher') {
						$body .= $user->school_class->class_teacher;
					}
				
					if (mail($to, $emailSubject, $body, $headers)) {
						$msg .= "Your email has been sent to " . $to . '<br />';
					}
				}
			}
		}
	}
}
//echo "<pre>"; print_r($_SESSION); print_r($_POST); echo "</pre>";
?>
<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Letter to Parents</title>
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
            .page-break {
            	page-break-after: always;
            }
            @media print {
            	.no-print {
            		display: none;
            	}
            }
            .letters .letter{
            	font-size: 14px;
            }
        </style>
        
        <script src="jquery-1.8.1.min.js"></script>
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
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1 class="no-print">Letter to Parents</h1>
        
	        <?
	        if (!empty($msg)) {
	        	echo $msg;
				exit;
	        }
			?>
			
			<? if ($_POST['method'] == 'print') : ?>
			<div align="center" class="no-print">
				<input type="button" value="Print" onclick="window.print()" />
			</div>
			<? endif; ?>
			
	        <div class="letters">
	        	<?        	
	        	if ($_POST['method'] == 'print') {
		        	foreach ($users as $user) {
		        		echo "Dear Parents of " . $user->first . ' ' . $user->last . ",<br /><br />";
						echo nl2br($_POST['content']) . "<br /><br />";
						echo "Sincerely,<br />";
						if ($signed == 'bc') {
							echo $signature;
						} else if ($signed == 'teacher') {
							echo $user->school_class->class_teacher;
						}
						echo "<br /><br /><div class='page-break'></div>";
		        	}
				} else if ($_POST['method'] == 'email') {
					echo "<form action='parent_letter3.php' method='post'>";
					
					if ($_POST['selection'] == 'letter') {
						echo "The following email will be sent out to all selected child(ren) that have email addresses in their profile:<br /><br /><br />";
						echo "<div class='letter'>Dear Parents of [student name],<br /><br />";
			    		echo nl2br($_POST['content']) . "<br /><br />";
						echo "Sincerely,<br />";
						if ($signed == 'bc') {
							echo $signature;
						} else if ($signed == 'teacher') {
							echo "[teacher name]";
						} 
					}
					echo "</div><input type='hidden' name='content' value='" . $_POST['content'] . "' />";
					echo "<input type='hidden' name='method' value='" . $_POST['method'] . "' />";
					echo "<input type='hidden' name='selection' value='" . $_POST['selection'] . "' />";
					if (isset($_POST['start'])) {
						echo "<input type='hidden' name='start' value='" . $_POST['start'] . "' />";
						echo "<input type='hidden' name='end' value='" . $_POST['end'] . "' />";
					}
					echo "<input type='hidden' name='action' value='" . $_POST['selection'] . "' /><br /><br />";
					?>
					
					<fieldset>
						<legend>Email properties</legend>
						<table>
							<tr>
								<td>Subject:</td>
								<td><input size="<?=strlen($_SESSION['schoolName'])+20?>" type="text" name="emailSubject" value="Message from <?=$_SESSION['schoolName']?>" /></td>
							</tr>
							<tr>
								<td>From:</td>
								<td><input size="30" type="text" name="from" id="from" value="" /> <i>must be a valid email address</i></td>
							</tr>
							<tr>
								<td>Reply to:</td>
								<td><input size="30" type="text" name="reply" id="reply" value="<?=$admin_email?>" /> <i>must be a valid email address</i></td>
							</tr>
						</table>
					</fieldset>
					
					<?
					echo "<br /><br /><input type='submit' name='email' id='email' value='send email' /></form>";
				}
	        	?>
	        </div>

	</body>
</html>