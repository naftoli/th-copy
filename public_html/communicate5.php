<?
session_start();
if (!isset($_SESSION['type']) || !isset($_SESSION['method']) || !isset($_SESSION['signature']) 
	|| !isset($_POST['submit'])) {
	header("Location: communicate.php");
}

$admin_auth = array('school'); 
require('header.php');

$schoolInfo = $_SESSION['school'];
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

$choice = $_SESSION['choice'];
switch ($choice) {
	case 1:
		$sql = "select * from users where school_id = " . $schoolID . " and user_registered > 0";
		break;
	case 2:
		$sql = "select * from users where class_id in (" . implode(',', $_SESSION['ids']) . ")";
		break;
	case 3:
		$sql = "select * from users where user_id in (" . implode(',', $_SESSION['ids']) . ")";
		break;
	case 4:
		$sql = "select u.* from users u 
				join rank_marks rm using (user_id) 
				where u.user_registered > 0 
				and rm.rank_ord = " . $_SESSION['rank']; 
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

$emailed = array();
if ($_SESSION['type'] == 'missions') {
	
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
	$userIDs = array();
	$userInfo = array();
	foreach ($users as $user) {
		$userIDs[] = $user->user_id;
		$userInfo[$user->user_id] = (array)$user;
	}
	require_once 'class.personalizedReport.php';
	$p = new PersonalizedReport($start, $end, $userIDs);
		
	$props = array(
		'signed'	=>	$signed,
		'signature'	=>	$signature, 
		'emailSubject'	=>	$emailSubject, 
		'from'		=>	$from, 
		'reply'		=>	$reply
	);
	
	$p->setEmailProps($props);
	$emailed = $p->createReport($userInfo, true);
	
} else if ($_SESSION['type'] == 'letter') {
	
	foreach ($users as $user) {
		$to = $user->email;
		//$to = 'naftolir@gmail.com'; 
		
		if (!empty($to)) {
			$body = "Dear Parents of " . $user->first . ' ' . $user->last . ",<br /><br />";
			$body .= nl2br($_SESSION['content']) . "<br /><br />";
			$body .= "Sincerely,<br />";
			if ($signed == 1) {
				$body .= $signature;
			} else if ($signed == 2) {
				$body .= $user->school_class->class_teacher;
			}
		
			if (mail($to, $emailSubject, $body, $headers)) {
				$emailed[] = $to;
			}
		}
	}
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
		
		<?
		if (empty($emailed)) {
			echo "No emails sent because no valid emails were found.";
		} else {
			echo "Emails were sent out to the following recipients:<br />";
			foreach ($emailed as $email) {
				echo $email . "<br />";
			}
		}
		?>
		
	</body>
</html>