<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

$ratio = gri('ratio', 100);
$auction_id = gri('auction_id', 0);
$auction_id = 23;

$student_numbers = array();
$sql = "SELECT count(*) AS total, school_id FROM users JOIN schools USING (school_id) WHERE users.user_registered > 0 GROUP BY school_id";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$student_numbers[$row['school_id']] = $row['total'];
}

$sql = "SELECT school_id, school_name, count(*) AS total FROM auction_winners JOIN users USING (user_id) JOIN schools USING (school_id) WHERE auction_id=" . $auction_id . " AND users.user_registered > 0 GROUP BY school_id ORDER BY school_name";
$query = mysql_query($sql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Auction Ratio'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
		
			<H1><?=T_('Auction Ratio')?></H1>
			<H2>
				<a href="admin_auction_revision.php">RETURN</a>
			</H2>
			
			<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
						
			<form method="post" action="admin_auction_ratio.php"  accept-charset="UTF-8">
				
				<input type="hidden" name="auction_id" value="<?=$auction_id;?>">
								
				<div>
					Ratio
					<input type="text" name="ratio" id="ratio" value="<?=$ratio;?>">
					<input type="submit" value="GO">
				</div>
				
				<table class="pretty_grid">
				
					<tr>
						<th align="left"><?=T_('School')?></th>
						<th align="left"><?=T_('Winners')?></th>
						<th align="left"><?=T_('# of Students')?></th>
						<th align="left"><?=T_('Ratio')?></th>
						<th align="left"></th>
					</tr>
					
					<? while ($row = mysql_fetch_assoc($query)) { ?>
						<? $scool_ratio = round((($row['total'] / $student_numbers[$row['school_id']]) * 100),2); ?>
						
						<? if ($scool_ratio <= $ratio) { ?>
						<tr>
							<td><?=$row['school_name'];?></td>
							<td><?=$row['total'];?></td>
							<td><?=$student_numbers[$row['school_id']];?></td>
							<td><?=$scool_ratio;?>%</td>
							<td><a href="admin_auction_new_winner.php?auction_id=<?=$auction_id;?>&school_id=<?=$row['school_id'];?>&ratio=<?=$ratio;?>&winners=<?=$row['total'];?>&students=<?=$student_numbers[$row['school_id']];?>">Enter New Winners</a></td>
						</tr>
						<? } ?>
						
					<? } ?>
				</table>
				
			</form>
		</DIV>
		
		<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
