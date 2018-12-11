<? 
$admin_auth = array('school'); 

require('header.php'); 

foreach($admin_user['auths']['school'] as $school_id) { 
	$sql = "SELECT s.school_name FROM schools AS s WHERE school_id=" . $school_id;
	$query = mysql_query($sql);	
	$row = mysql_fetch_assoc($query);
	$school_name = $row['school_name'];
	echo "SCHOOL ID:" . $school_id . " SCHOOL NAME:" . $school_name . "<br />";
}

//$subjects_sql = "SELECT ss.subject_id, s.subject_name FROM school_subjects AS ss JOIN subjects AS s USING (subject_id) WHERE school_id=" . $school_id;
//$subjects_query = mysql_query($subjects_sql);

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
			
			<H1>
				<?=T_('Assign Cards')?>
			</H1>
						
			<? if (!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
						
			<? include('admin_footer.php'); ?>
			
		</DIV> <!-- body -->
				
	</BODY>
	
</HTML>
