<?
$admin_auth = array('user'); 
require('header.php');

//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
//make sure all values are ints
$clean = true;
foreach ($_POST as $k => $v) {
	//echo $k . ' - ' . $v . "<br />";
	if (!in_array($k, array('user', 'task', 'submit')) && ($v != 0) && !is_numeric($v)) {
		$clean = false;
		break;
	}
}
if ($clean) {
	if (strpos($_POST['user'], ':')) {
		$users = explode(":", $_POST['user']);
		unset($users[count($users)-1]);
	} else {
		$users = array($_POST['user']);
	}
	//print_r($users); exit;
	$start = $_POST['start'];
	$end = $_POST['end'];
	
	$userInfo = array();
	$sql = "select * from users u 
			join schools s using (school_id) 
			join classes c on (u.class_id = c.class_id) 
			where user_id in (" . implode(',', $users) . ") 
			and user_registered > 0";
	//echo $sql;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$userInfo[$row['user_id']] = $row;
	}
	
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
	header("Location: parentModifyReport.php?msg=" . urlencode($error));
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