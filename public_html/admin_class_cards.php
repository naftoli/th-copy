<? 
$admin_auth = array('class'); 

require('header.php'); 

foreach($admin_user['auths']['class'] as $class_id) { 
	$sql = "SELECT c.school_id, s.school_name FROM classes AS c JOIN schools AS s USING (school_id) WHERE class_id=" . $class_id;
	$query = mysql_query($sql);	
	$row = mysql_fetch_assoc($query);
	$school_id = $row['school_id'];
	$school_name = $row['school_name'];
}
//echo "CLASS ID:" . $class_id . " SCHOOL ID:" . $school_id . " SCHOOL NAME:" . $school_name . "<br />";

$subjects_sql = "SELECT ss.subject_id, s.subject_name FROM school_subjects AS ss JOIN subjects AS s USING (subject_id) WHERE school_id=" . $school_id;
$subjects_query = mysql_query($subjects_sql);

foreach($admin_user['inst_ids'] as $inst_id) { 
	echo "INST ID:" . $inst_id . "<br />";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Assign Cards'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
		</script>
	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
			
			<DIV class="left_menu">
				<?include('admin_inc.php');?>
			</DIV>
			
			<H1>
				<?=T_('Assign Cards')?>
			</H1>
						
			<? if (!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
						
			<label>School:<?=$school_name;?></label>
			
			<br />
			
			<label>Subject:
			<select name="subject_id" id="subject_id">
			<? while ($row = mysql_fetch_assoc($subjects_query)) { ?>
				<option value="<?=$row['subject_id'];?>"><?=$row['subject_name'];?></option>
			<? } ?>
			</select>
			</label>
			
			<? include('admin_footer.php'); ?>
			
		</DIV> <!-- body -->
				
	</BODY>
	
</HTML>
