<?php
$admin_auth = array('school');
require('header.php');
$admin_id = $admin_user['admin_id'];

$sql = "select id from admin_auths where admin_id = " . $admin_id;
$res = mysql_query( $sql );
$r = mysql_fetch_assoc( $res );
$school_id = $r['id'];

//check for hebrew schools
$h_school = false;
$sql = "select inst_id from schools where school_id = " . $school_id;
$res = mysql_query( $sql );
$row = mysql_fetch_assoc( $res );
$inst_id = $row['inst_id'];
if ( $inst_id == 4 ) {
    $h_school = true;
}

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

// PROGRAM DIRECTOR //
$sql = "SELECT title, first, last FROM admins where admin_id = " . $admin_id;
//echo $sql;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$director_title = $row["title"];
$director_first = $row["first"];
$director_last = $row["last"];
    
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
		<STYLE type="text/css">
		</STYLE>
		
		<script>
		</script>
		
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
			<?
			if ( $h_school ) 
                $action = "admin_setup_guide_hschool.php";
            else 
                $action = "admin_setup_guide.php";
			?>
			<FORM name="setup_guide" method="post" action="<?=$action?>">
				<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
				<input type="button" value="RETURN" onclick="document.forms['setup_guide'].submit();">
			</FORM>
			</DIV>
			
			<br />
					
			<DIV style="position:absolute; left:50px;">
			
				<? for ($cno = 0; $cno < count($children); $cno++) : ?>
					<? $child = $children[$cno]; ?>
						
					<DIV style="text-align:left;">	
						Base: <?=$child->school->school_name;?>
						<br />
						<? $grade = (empty($child->school_class->class_sub) ? $child->school_class->class_grade : $child->school_class->class_grade . '-' . $child->school_class->class_sub);?>
						Platoon: <?=$grade?>
						<br />
						Commander: <?=$child->school_class->class_teacher;?>
						<br /><br />
						To the parents of: <?=$child->first;?> <?=$child->last;?>
					</DIV>					
					<br />
					<br />
					<!--
					<DIV style="text-align:left;">
						Dear Parent(s),
					</DIV>
					<br />
					-->
					<DIV style="text-align:left;">
						I would like every parent in our school to have a Tzivos Hashem parent account. This account will give you the ability to:
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;1. Print and mark your child’s missions
					</DIV>
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;2. Track your child’s progress
					</DIV>
					<!--
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;3. Customize tasks on the mission sheets
					</DIV>
					-->
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;3. Sign up for weekly emails of their progress
					</DIV>
					<br />
					<br />
					
					<DIV style="text-align:left;">
						You access your parent account from mashpia.com.
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;• If you already have a parent account, enter your user name and password to login.<br />
						&nbsp;&nbsp;&nbsp;&nbsp;• If you do not know your username or password, click on ‘Forgot Username or Password’, enter your e-mail address, and you will receive an email with your login information.<br />
						&nbsp;&nbsp;&nbsp;&nbsp;• If you do not have a parent account, click on 'Create a Parent Account' and provide the information requested.<br /> 
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Next, add your child to your account by entering his/her 20 digit account number: 3<?=$child->user_code;?>.
					</DIV>				
					<br />
					<br />
 					
					<DIV style="text-align:left;">
						Sincerely,<br /><br />
					</DIV>
					
					<DIV style="text-align:left;">
						<br />
						<?=$director_title;?> <?=$director_first;?> <?=$director_last;?><br />
						Base Commander
					</DIV>
						
					<DIV style="height:100px;">
					</DIV>
					
					<DIV style="page-break-after:always">
					</DIV>
				<? endfor; ?>									
				
			</DIV>

<? else : ?>
			<FORM method="post" action="parent_children_barcodes.php">
			<input type="hidden" name="action" value="print">
			<?=$select;?>
			<input type="submit" value="GO">
			</FORM>
<? endif; ?>			

		
	</BODY>
	
</HTML>
