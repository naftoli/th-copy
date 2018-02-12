<? 
$admin_auth = array('school'); 

require('header.php'); 
require_once('calendar.php');

$school_id = gri('school_id', -1);
$action = gr('action', '');

$bidders = array();
$prizes_available = 0;
$total_bidders = 0;

// ***** Schools ***** //
$schools_sql = 'SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name';
$schools_query = mq($schools_sql);
// ***** Schools ***** //

// ***** Auctions that have not been ran ***** //
if ($school_id == -1) {
	$auctions_sql = "SELECT auction_id, auction_date, school_id FROM auctions WHERE auction_ran=0 AND school_id IS NULL ORDER BY auction_date DESC";
}
else {
	$auctions_sql = "SELECT auction_id, auction_date, school_id FROM auctions WHERE auction_ran=0 AND school_id=" . $school_id . " ORDER BY auction_date DESC";
}
$auctions_query = mq($auctions_sql);

if ($action != "") {
	//////////$auction_id = gri('auction_id', -1);
	$auction_id = 23;
	
	$prizes_sql = "SELECT * FROM auction_prizes AS ap JOIN prizes_auction AS pa USING (prize_id) WHERE auction_id=" . $auction_id . " ORDER BY pa.prize_points DESC, ap.available DESC";
	$prizes_query = mq($prizes_sql);
}

function get_invalid_auctions() {
	global $auction_id;
	
	$sql_or = "";
	
	$sql = "SELECT auction_id FROM auctions WHERE auction_id < " . $auction_id . " AND auction_ran=1 AND approved=1 ORDER BY auction_id DESC LIMIT 2";
	$query = mysql_query($sql);
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		if ($row_num == 1)
			$sql_or = "(auction_id =" . $row['auction_id'];
		else
			$sql_or = $sql_or . " OR auction_id=" . $row['auction_id'];
	}
	$sql_or = $sql_or . " OR auction_id=" . $auction_id . ")";
	
	return $sql_or;
}

function has_user_won($user_id, $invalid_auctions) {
	global $auction_id;

	$sql = "SELECT user_id FROM auction_winners WHERE user_id=" . $user_id . " AND " . $invalid_auctions;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows > 0)
		return true;
	else
		return false;
	
}

function get_winners($prize, $number_of_prizes_available, $number_of_bidders) {
	global $auction_id;
	global $school_id;
	global $bidders;
	
	$winner_found = false;
	
	$invalid_auctions = get_invalid_auctions();
	
	$sql = "SELECT user_id FROM auction_user_prizes WHERE auction_id=" . $auction_id . " AND prize_id=" . $prize['prize_id'] . " AND user_id NOT IN (SELECT user_id FROM auction_winners WHERE " . $invalid_auctions . ")";
	
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	if ($num_rows > 0) {
		$winner_found = true;
	}
	else {
		$winner_found = false;
	}
	
	//echo "NUMBER OF BIDDERS:" . count($bidders) . "<br />";
	
	//if ($num_rows > 0) {
	//	$winner_found = true;
		//$row = mysql_fetch_assoc($query);
		//$sql = "INSERT INTO auction_winners_test (auction_id, user_id, prize_id, quantity) VALUES (" . $auction_id . ", " . $row['user_id'] . ", " . $prize['prize_id'] . ", 1)";
		//echo $sql . "<br />";
		//mq("INSERT INTO auction_winners_test (auction_id, user_id, prize_id, quantity) VALUES (" . $auction_id, ", " . $row['user_id'] . ", " . $prize['prize_id'] . ", 1)");
	//}
	//else {
	//$winner_found = false;
		//if ($prizes_available < $number_of_bidders)
		//	$prizes_available = $number_of_bidders;
			
		//$winner = rand(1, $number_of_bidders);
		//$winner--;
		//$winner_id = $bidders[$winner_id];
		
		//$sql = "SELECT * FROM auction_winners WHERE auction_id=" . $auction_id . " prize_id=";
		//mq("INSERT INTO auction_winners_test (auction_id, user_id, prize_id, quantity) VALUES (" . $auction_id, ", " . $bidders[$winner] . ", " . $prize['prize_id'] . ", 1)");
		//$sql = "INSERT INTO auction_winners_test (auction_id, user_id, prize_id, quantity) VALUES (" . $auction_id . ", " . $bidders[$winner] . ", " . $prize['prize_id'] . ", 1)";
		//echo $sql . "<br />";
		
		//echo $sql . "<br />";
		
	//}
	
	
	//echo $sql . "<br />";
	//$bidders_sql = "SELECT user_id FROM auction_user_prizes WHERE auction_id=" . $auction_id . " AND prize_id=" . $prize['prize_id'];	
	//$bidders_query = mq($bidders_sql);
	//while ($bidder = mysql_fetch_assoc($bidders_query)) {
		// ***** Check to see if the user has won anything in this auction or the previous two auctions ***** //
		//$has_won = has_user_won($bidder['user_id'], $invalid_auctions);
		
		//if ($has_won == true) {
		//	$winner_found = false;
		//}
		//else {
		//	$winner_found = true;
		//}
	//}

	return $winner_found;
}

function get_number_of_bidders($prize_id){
	global $auction_id;
	global $bidders;
	
	$bidders = array();
	
	$sql = "SELECT user_id FROM auction_user_prizes WHERE auction_id=" . $auction_id . " AND prize_id=" . $prize_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	while ($row = mysql_fetch_assoc($query)) {
		array_push($bidders, $row['user_id']);
	}
	
	return $num_rows;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Run Chinese Auction'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
					
			<DIV class="left_menu">
				<?include('admin_inc.php');?>
			</DIV>
			
			<H1>
				<?=T_('Run Chinese Auction')?>
			</H1>
			
<? if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : ?>

	
			<FORM action="admin_run_auction.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				
				<P>
					<?=T_('Select Institution')?>: 
					<SELECT name="school_id">
						<? if ($admin_user['auth'] == 'super') : ?>
							<OPTION value="-1">&lt;<?=T_('Multi institution auctions')?>&gt;
						<? endif; ?>
						<? while ($school = mysql_fetch_assoc($schools_query)) : ?>
							<? if ($school['school_id'] == $school_id) { ?>
							<OPTION selected value="<?=$school['school_id']?>"><?=es($school['inst_name'])?> - <?=es($school['school_name'])?></OPTION>
							<? } else { ?>
							<OPTION value="<?=$school['school_id']?>"><?=es($school['inst_name'])?> - <?=es($school['school_name'])?></OPTION>
							<? } ?>
						<? endwhile; ?>
					</SELECT> 
					<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
				</P>
				
				<P>
					<?=T_('Auction')?>: 
					
					<SELECT name="auction_id" dir="rtl">
						<? while($auction = mysql_fetch_assoc($auctions_query)) : ?>
							<? if ($auction['auction_id'] == $auction_id) { ?>
							<OPTION selected value="<?=$auction['auction_id']?>"><?=es(dateToHebrew($auction['auction_date']))?></OPTION>
							<? } else { ?>
							<OPTION value="<?=$auction['auction_id']?>"><?=es(dateToHebrew($auction['auction_date']))?></OPTION>
							<? } ?>
						<? endwhile; ?>
					</SELECT> 
					<INPUT class="submit" type="submit" value="<?=T_('Go')?>" onclick="document.getElementById('action').value='run';">
				</P>				
			</FORM>
			
			<HR>
			
<? endif; ?> <!-- if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : -->
			
<? if ($action != "") : ?>		

			<table class="pretty_grid">
				<tr>
					<th><?=T_('Prize');?></th>
					<th><?=T_('Points');?></th>
					<th></th>
					<th><?=T_('Available');?></th>
					<th><?=T_('Bidders');?></th>
					<th><?=T_('Winners');?></th>
					<th><?=T_('Not awarded');?></th>
				</tr>
				<? while ($prize = mysql_fetch_assoc($prizes_query)) : ?>
				<? 
					$number_of_bidders = get_number_of_bidders($prize['prize_id']);
					$total_bidders = $total_bidders + $number_of_bidders;
					
					$number_of_prizes_available =  $prize['available'];
					$prizes_available = $prizes_available + $number_of_prizes_available; 
					$winners_found = 0;
					$winners_not_found = 0;
										
					for ($cntr = 0; $cntr < $number_of_prizes_available; $cntr++) {
						$winner_found = get_winners($prize, $number_of_prizes_available, $number_of_bidders);
					//	if ($winner_found == true)
					//		$winners_found++;
					//	else
					//		$winners_not_found++;
					} 
				?>
				
				<tr>
					<td><?=$prize['prize_name'];?> <?=$prize['prize_id'];?></td>
					<td><?=$prize['prize_points'];?></td>
					<td></td>
					<td style="text-align:center;"><?=$prize['available'];?></td>
					<td style="text-align:center;"><?=$number_of_bidders;?></td>
					<td style="text-align:center;"><?=$winners_found;?></td>
					<td style="text-align:center;"><?=$winners_not_found;?></td>
				</tr>
				<? endwhile; ?> <!-- while ($prize = mysql_fetch_assoc($prizes_query)) -->
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td style="text-align:center;"><?=$prizes_available;?></td>	
					<td style="text-align:center;"><?=$total_bidders;?></td>	
					<td></td>
					<td></td>					
				</tr>
			</table>
			
<? endif; ?> <!-- if ($action != "") -->
			
		</DIV> <!-- body -->
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
