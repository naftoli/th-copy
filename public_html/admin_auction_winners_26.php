<? 
$admin_auth = array('school');
require('header.php'); 
require_once('calendar.php'); 

$auction_id = 26;
assure_id_school('school_id');
$school_id = gri('school_id', -2);

$action = gr('action');

if ($auction_id != -1) 
{
	$winners = "SELECT users.first, users.last, user_id, user_serial, school_name, school_number, classes.class_grade, classes.class_sub, prizes_auction.prize_name, prize_number, prize_id, auction_winners.quantity ";
	$winners = $winners . "FROM auction_winners ";
	$winners = $winners . "JOIN users USING (user_id) ";
	$winners = $winners . "JOIN prizes_auction USING (prize_id) ";
	$winners = $winners . "JOIN auctions USING (auction_id) ";
	$winners = $winners . "LEFT JOIN schools ON (users.school_id = schools.school_id) ";
	$winners = $winners . "LEFT JOIN classes USING (class_id) ";
	$winners = $winners . "WHERE auction_id=26 ";
	$winners = $winners . "ORDER BY prizes_auction.prize_number, auction_winners.prize_id, school_name, classes.class_grade, classes.class_sub, class_id, users.last, users.first, auction_winners.auction_id";
	
	//$entries = "SELECT auction_user_prizes.user_id, first, users.last, user_id, user_serial, school_name, school_number, class_grade, class_sub, prize_name, prize_number, prize_id, quantity ";
	//$entries = $entries . "FROM auction_user_prizes ";
	//$entries = $entries . "JOIN users USING (user_id) ";
	//$entries = $entries . "JOIN prizes_auction USING (prize_id) ";
	//$entries . $entries . "JOIN auctions USING (auction_id) ";
	//$entries = $entries . "LEFT JOIN schools ON (users.school_id = schools.school_id) ";
	//$entries = $entries . "LEFT JOIN classes USING (class_id) ";
	//$entries = $entries . "WHERE auction_id=26 ";
	//$entries = $entries . " ORDER BY school_name, users.school_id, prize_number, prize_id, school_name, class_grade, class_sub, class_id, last, first, auction_id";

	//if ($action == 'export_winners') 
	//{
	//	require_once('export.php');
	//	export($winners, 'winners');
	//	exit;
	//}
	
	//if ($action == 'export_entries') 
	//{
	//	require_once('export.php');
	//	export($entries, 'entries');
	//	exit;
	//}

	$winners = mq($winners);
	//$entries = mq($entries);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('View Chinese Auction Winners'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			<H1><?=T_('View Chinese Auction Winners')?></H1>
			
			<? if (!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
			
			<? if (isset($winners)) : ?>
			
				<!--
				<A HREF="admin_auction_winners_26.php?school_id=<?//=$school_id?>&amp;action=export_winners&amp;auction_id=<?//=$auction_id?>">Export winners</A>
				-->
				
				<TABLE class="pretty_grid">
				
					<CAPTION><?=T_('Winners')?></CAPTION>
					
					<TR>
						<TH><?=T_('Prize #')?></TH>
						<TH><?=T_('Prize')?></TH>
						<TH><?=T_('School #')?></TH>
						<TH><?=T_('School Name')?></TH>
						<TH><?=T_('Grade')?></TH>
						<TH><?=T_('Name')?></TH>
						<TH><?=T_('Quantity')?></TH>
					</TR>
					
				<?while($row = mysql_fetch_assoc($winners)):?>
					<TR>
						<TD><?=$row['prize_number']?></TD>
						<TD><?=es($row['prize_name'])?></TD>
						<TD><?=$row['school_number']?></TD>
						<TD><?=es($row['school_name'])?></TD>
						<TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
						<TD><?=es($row['first'])?> <?=es($row['last'])?></TD>
						<TD><?=$row['quantity']?></TD>
					</TR>
				<?endwhile;?>
				
				</TABLE>
			<? endif; ?>
			
			<BR>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
