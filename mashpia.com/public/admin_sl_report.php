<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

assure_id_school('school_id');

$sql = "SELECT * FROM users AS u JOIN rank_marks AS rm ON (u.user_id=rm.user_id AND rm.rank_ord=4) JOIN schools USING (school_id) JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id) WHERE u.user_registered > 0";
$query = mysql_query($sql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_("Second Leutenant Report"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
			
		<DIV CLASS="body">

			<H1>
				<?=T_("Second Leutenant Report")?>
			</H1>
				
			<? if(!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>

			<TABLE class="pretty_grid">
				<THEAD>
					<TR>
						<TH>School</TH>
						<TH>Name</TH>
						<TH>Grade</TH>
					</TR>
				</THEAD>
				
				<? while ($row = mysql_fetch_assoc($query)) { ?>
				<tr>
					<td><?=$row['school_name'];?></td>
					<td><?=$row['first'];?> <?=$row['last'];?></td>
					<td><?=$row['class_grade'];?></td>						
				</tr>
				<? } ?>
			</TABLE>


		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
