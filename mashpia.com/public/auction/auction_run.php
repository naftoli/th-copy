<?
$admin_auth = array('school'); 
require('../header.php');  
require('../calendar.php');
require('auction.php');

$beginning_of_hebrew_year = beginning_of_hebrew_year();
$status = "RUN AUCTION";

if (isset($_POST['auction_id']))
{
	$auction_id = $_POST['auction_id'];

	require('auction_prize.php');
	require('auction_user_prize.php');
	
	//before running distribution need to find out which prizes to distribute to
	//run ticket distribution first
	//require('ticket_distribution.php');
	//$ticket_distribution = new ticket_distribution($auction_id);
	
	$sql = "SELECT * FROM auctions WHERE auction_id=" . $auction_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$auction = new auction($row);
	
	//$auction->ratio();
	
	$auction->get_small_prizes();
	$auction->award_small_prizes();
	
	$auction->get_big_prizes();
	$auction->award_big_prizes();
	
	$status = "AUCTION RAN"; 	 
}

$auctions = array();
$sql = "SELECT * FROM auctions WHERE auction_ran=0 ORDER BY auction_id DESC"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$auction_select = new auction($row);
	array_push($auctions, $auction_select);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<TITLE>
			Run Auction - Tzivos Hashem Management System
		</TITLE>
		
		<LINK href="../admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<? include('../admin_header.php'); ?>
		
		<DIV CLASS="body">
			<H1>Run Chinese Auction</H1>	
		</DIV>
		
		<P>
			<FORM method="post" action="auction_run.php">
				
				<!-- ********** AUCTION ID ********** -->
				<SELECT name="auction_id" dir="rtl">
					<? foreach ($auctions as $auction_select) : ?>
						<? if ($auction_id == $auction_select->auction_id) : ?>
						<OPTION selected value="<?=$auction_select->auction_id;?>"><?=$auction_select->auction_date;?> - <?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
						<? else : ?>
						<OPTION value="<?=$auction_select->auction_id;?>"><?=$auction_select->auction_date;?> - <?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
						<? endif; ?>						
					<? endforeach; ?>
				</SELECT> 
				<!-- ********** AUCTION ID ********** -->
				
				<input type="submit" value="<?=$status;?>">
			</FORM>
		</P>
			
		<br />
		
	</BODY>
	
</HTML>
