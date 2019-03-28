<?php
$admin_auth = array('school');
require('header.php');
$admin_id = $admin_user['admin_id'];

$sql = "select id from admin_auths where admin_id = " . $admin_id;
$res = mysql_query( $sql );
$r = mysql_fetch_assoc( $res );
$school_id = $r['id'];

$action = isset($_POST['action']) ? $_POST['action'] : "";
$children = array();
$select = '';

if ($action == "") {			
	$sql = "SELECT s.school_id, s.school_name ";
	$sql = $sql . "FROM admin_auths AS aa ";
	$sql = $sql . "JOIN schools AS s ON (aa.id = s.school_id) ";
	$sql = $sql . "WHERE aa.admin_id=" . $admin_id . " AND aa.auth = 'school'";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	if ($num_rows > 1) {
		$select = "<SELECT name='school_id'>";
		while ($row = mysql_fetch_assoc($query)) {
			$select = $select . "<OPTION value='" . $row['school_id'] . "'>" . $row['school_name'] . "</OPTION>";
		}
		$select = $select . "</SELECT>";
	}
	else {
		$row = mysql_fetch_assoc($query);
		produce_report($row['school_id']);
	}
}
else {
	produce_report(0);	
}
    
function produce_report($school_id) {
	global $children;
		
	if ($school_id == 0) $school_id = $_POST['school_id'];
	
	include("camps/includes/classes/user.php");
	include("camps/includes/classes/admin.php");
	$sql = "SELECT u.* ";
	$sql = $sql . "FROM users AS u ";
	$sql = $sql . "JOIN classes AS c USING (class_id) ";
	$sql = $sql . "WHERE u.school_id=" . $school_id . " AND u.class_id > 0 ";
	$sql = $sql . "ORDER BY c.class_grade, c.class_sub ";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$child = new user($row);
		$child->get_childs_parent();
		$child->get_school_class();
		$child->get_school();
		array_push($children, $child);
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<TITLE>BARCODES</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">		
		<style type="text/css">
			noprint {
				.noprint { display: none; }
			}
		</style>
		
	</HEAD>
	
	<BODY>
<? if (count($children) > 0) : ?>
<DIV class="noprint">
			<input type="button" value="PRINT" onclick="window.print();"> 
			</DIV>
			
			<br />
					
			<DIV style="position:absolute; left:50px;text-align:left;">
			
				<? for ($cno = 0; $cno < count($children); $cno++) : ?>
					<? $child = $children[$cno]; ?>
						
					<? if ($child->childs_parent->admin_id) : ?>
					
					<div>

<? $grade = (empty($child->school_class->class_sub) ? $child->school_class->class_grade : $child->school_class->class_grade . '-' . $child->school_class->class_sub);?>

School Name: <?=$child->school->school_name;?><br />
Platoon: <?=$grade?><br />
Subject: New Tzivos Hashem Site!
<br /><br />
Dear Parent, 
<br /><br />
We are excited to share with you the launch of the brand new Tzivos Hashem Mobile Site!
<br /><br />
With it, you’ll able to mark you children’s missions daily, straight from any smartphone (or computer). You’ll also be able to check in on their progress reports, personalize their growth, and stay up-to-date on Tzivos Hashem news from bases around the world.  
<br /><br />
Darchei Hachassidus will come alive in your home as managing your kids’ Chayolei Tzivos Hashem accounts becomes easier than ever. 
<br /><br />
You already have a Parent account! Your child <b><?=$child->first . ' ' . $child->last?></b> is linked to your account. All you need to do is log on to mashpia.com/mobile. Below is your login information: 
<br /><br />
Username: <?=$child->childs_parent->username?>
Password: <?=$child->childs_parent->password?>
<br /><br />
Set aside one minute each night before your children goes to sleep to mark their missions with them and help your young soldier reach the greatest heights in Hashem’s army.
<br /><br />
Please make sure your phone has a kosher filter on it, and never leave your phone in your children’s hands without your supervision. 
<br /><br />
May you have much continued Nachas from all your children.
<br /><br />
For any questions, help, or feedback, contact your Base Commander today.
<br /><br />
Sincerely, 
<br /><br />
<?=$child->school_class->class_teacher;?>

<? else : ?>

<div>
<? $grade = (empty($child->school_class->class_sub) ? $child->school_class->class_grade : $child->school_class->class_grade . '-' . $child->school_class->class_sub);?>	
School Name: <?=$child->school->school_name;?><br />
Platoon: <?=$grade?><br />
Subject: New Tzivos Hashem Site!
<br /><br />
Dear Parent,
<br /><br /> 
We are excited to share with you the launch of the brand new Tzivos Hashem Mobile Site!
<br /><br />
With it, you’ll able to mark you children’s missions daily, straight from any smartphone (or computer). You’ll also be able to check in on their progress reports, personalize their growth, and stay up-to-date on Tzivos Hashem news from bases around the world.
<br /><br />
Darchei Hachassidus will come alive in your home as managing your kids’ Chayolei Tzivos Hashem accounts becomes easier than ever. 
<br /><br />
All you need to do is create an account. Visit mashpia.com/mobile today.
<br />
<ul>
	<li>Click on “Create an account”</li>
	<li>Click on add child</li>
	<li>You will be asked if your child has been registered to Tzivos Hashem before, answer: Yes</li>
	<li>
		Choose
		<? $grade = (empty($child->school_class->class_sub) ? $child->school_class->class_grade : $child->school_class->class_grade . '-' . $child->school_class->class_sub);?>
		<ul>
			<li>School name: <?=$child->school->school_name;?></li>
			<li>Class: <?=$grade?></li>
			<li>Last Name: <?=$child->last?></li>
			<li>Date of birth: <?=$child->dob?></li>
		</ul>
		<li>Link child to my account</li>
	</li>
</ul>
<br />          
Set aside one minute each night before your children goes to sleep to mark their missions with them and help your young soldier reach the greatest heights in Hashem’s army.
<br /><br />
Please make sure your phone has a kosher filter on it, and never leave your phone in your children’s hands without your supervision. 
<br /><br />
May you have much continued Nachas from all your children
<br /><br />
For any questions, help, or feedback, contact your Base Commander today.
<br /><br /> 
<?=$child->school_class->class_teacher;?><br />
						
					<? endif; ?>
					
				</div>
				<div style="page-break-after: always;"></div>
					
				<? endfor; ?>									
				
			</DIV>

<? endif; ?>			

		
	</BODY>
	
</HTML>
