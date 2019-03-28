<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

$sql = "SELECT awd.*, pa.prize_name, pa.prize_number, pa.prize_points, u.first, u.last, c.class_grade FROM auction_winners_deleted AS awd JOIN prizes_auction AS pa USING (prize_id) JOIN users AS u USING (user_id) JOIN classes AS c USING (class_id) ORDER BY prize_name";
$query = mysql_query($sql);

$row_num = 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Auction Winners Deleted'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
	</HEAD>
	
	<BODY>
		
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
		
			<DIV class="body_header_left">			
				<A HREF="admin.php"><?=T_('Home page')?></A>
			</DIV>
			
			<DIV class="body_header_right">
				<A HREF="logout.php"><?=T_('Logout')?></A>
			</DIV>
			
			<DIV class="left_menu">
				<?include('admin_inc.php');?>
			</DIV>
			
			
			<br />
			
			<table class="pretty_grid">
				<tr>
					<th align="left">&nbsp;</th>
					<th align="left"><?=T_('AUCTION')?></th>
					<th align="left"><?=T_('USER ID')?></th>
					<th align="left"><?=T_('NAME')?></th>
					<th align="left"><?=T_('GRADE')?></th>
					<th align="left"><?=T_('PRIZE ID')?></th>
					<th align="left"><?=T_('PRIZE NAME')?></th>
					<th align="left"><?=T_('PRIZE NUMBER')?></th>
					<th align="left"><?=T_('PRIZE POINTS')?></th>
				</tr>			
				
				
				<? while ($row = mysql_fetch_assoc($query)) {  $row_num++; ?>
				<tr>
					<td><?=$row_num;?></td>
					<td><?=$row['auction_id'];?></td>
					<td><?=$row['user_id'];?></td>
					<td><?=$row['first'];?> <?=$row['last'];?></td>
					<td><?=$row['class_grade'];?></td>
					<td><?=$row['prize_id'];?></td>
					<td><?=$row['prize_name'];?></td>
					<td><?=$row['prize_number'];?></td>
					<td><?=$row['prize_points'];?></td>
				</tr>
				<? } ?>
			</table>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
