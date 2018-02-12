<?
$admin_auth = array('school'); 
require('header.php');

//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
//make sure all values are ints
$clean = true;
foreach ($_POST as $k => $v) {
	//echo $k . ' - ' . $v . "<br />";
	if (!in_array($k, array('task', 'submit')) && ($v != 0) && !is_numeric($v)) {
		$clean = false;
		break;
	}
}
if ($clean) {
	$school = $_POST['school'];
	$class = isset($_POST['class']) ? $_POST['class'] : 0;
	$user = isset($_POST['user']) ? $_POST['user'] : 0;
	$start = $_POST['start'];
	$end = $_POST['end'];
	
	//figure out which users need report
	$users = array();
	$userInfo = array();
	if ($user > 0) {
		$users[] = $user;
	} else {
		if ($class > 0) {
			$type = "class";
		} else {
			$type = "school";
		}
		$sql = "select * from users u 
				join schools s using (school_id) 
				join classes c using (class_id) 
				where u.{$type}_id = " . $$type . " 
				and u.user_registered > 0";
		//echo $sql;
		$result = mysql_query($sql);
		$users = array();
		while ($row = mysql_fetch_assoc($result)) {
			$users[] = $row['user_id'];
			$userInfo[$row['user_id']] = $row;
		}
	}
	//echo "<pre>"; print_r($users); echo "</pre>"; exit;
	
	require_once 'class.personalizedReport.php';
	if ($_POST['reportType'] == 1) {
		//create report for each user showing selected tasks
		$subject = $_POST['subject'];
		$tasks = array();
		foreach ($_POST['task'] as $task) {
			$tasks[] = rawurldecode($task);
		}
		$r = new PersonalizedReport($start, $end, $users, $subject, $tasks);
	} else {
		//create report for each user showing active tasks
		$r = new PersonalizedReport($start, $end, $users);
	}
} else {
	$error = "You have an error in your selection, please try again!";
	header("Location: personalizedReport.php?msg=" . urlencode($error));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Personalized Progress Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 6px;
                border: 1px solid black;
                vertical-align: middle;
            }
            td.heading {
            	height: 30px;
            	color: white;
            	background-color: black;
            	font-size: 14px;
            }
            .sticker {
            	text-align: center;
            }
            .sticker img {
            	height: 40px;
            }
            td.total {
            	text-align: center;
            	width: 80px;
            }
            div.info {
            	line-height: 1.4;
            	margin-bottom: 10px;
            }
            @media print {
            	.noPrint {
            		display: none;
            	}
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="noPrint">Personalized Progress Report</h1>
        
        <? $r->createReport($userInfo); ?>
	</body>
</html>