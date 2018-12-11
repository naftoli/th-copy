<?
$admin_auth = array('school'); 
require('header.php');

if (!isset($_POST['submit'])) {
	header("Location: parent_letter.php");
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Parent Letter</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Parent Letter</h1>
        
        <?
        $ids = array();
		foreach ($_POST as $k => $v) {
			if (is_int($k)) {
				$ids[] = $k;
			}
		}

        switch ($_POST['choice']) {
			case 1:
				$type = 'class';
				break;
			case 2:
				$type = 'user';
				break;
			case 3:
				$type = 'school';
				break;
		}
		
		switch ($_POST['signature']) {
			case 1:
				$signed = 'bc';
				break;
			case 2:
				$signed = 'teacher';
				break;
		}
		
        $users = array();
		//require_once("classes/admin.php");
		require_once("classes/user.php");
		$sql = "select * from users where {$type}_id in (" . implode(",", $ids) . ")";
		//echo $sql;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$child = new user($row);
			$child->get_childs_parent();
			$child->get_school_class();
			$child->get_school();
			array_push($users, $child);
		}
        ?>
       	
       	<? foreach ($users as $child) : ?>
       	
	       	<DIV>	
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
			
			<?=$_POST['letter']?> 
			The number is: 3<?=$child->user_code?>.
			<br />
			<br />
			<DIV style="text-align:left;">
				Sincerely,<br /><br />
			</DIV>
			
			<DIV style="text-align:left;">
				<br />
				<? if ($_POST['signature'] == 1) : ?>
					<?=$director_title;?> <?=$director_first;?> <?=$director_last;?><br />
					Base Commander
				<? elseif ($_POST['signature'] == 2) : ?>
					<?=$child->school_class->class_teacher;?><br />
					Teacher
				<? endif; ?>
			</DIV>
						
			<DIV style="height:100px;">
			</DIV>
			
			<DIV style="page-break-after:always">
			</DIV>
			
		<? endforeach; ?>
	</body>
</html>