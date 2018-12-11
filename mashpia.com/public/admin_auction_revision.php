<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

if ($admin_user['auth'] != 'super') {
	header("Location:admin.php");
}

$user_prize_ids = gr("user_prize_ids", "");
$current_auction = gr("current_auction", 0);

if ($user_prize_ids != "" && $current_auction > 0) {	
	$deletions = explode(":", $user_prize_ids);
	for ($cntr = 0; $cntr < count($deletions); $cntr++) {
		$user_prize_id = explode(";", $deletions[$cntr]);		
		$user_id = $user_prize_id[0];
		$prize_id = $user_prize_id[1];
				
		//mq("INSERT INTO auction_winners_deleted (auction_id, user_id, prize_id) VALUES (" . $current_auction . ", " . $user_id . ", " . $prize_id . ")");		
		mq("DELETE FROM auction_winners WHERE auction_id=" . $current_auction . " AND user_id=" . $user_id . " AND prize_id=" . $prize_id);
		
		//echo "<input type='hidden' name='delete_sql' value='" . $delete_sql . "'>\n";	
	}
}


// ********** Last Two Auctions (approved) ********** //
$auctions = array();
$last_auction = 0;
$sql = "SELECT * FROM auctions WHERE approved=1 ORDER BY auction_date DESC LIMIT 2";
$query = mysql_query($sql);
$row_num = 0;
while ($row = mysql_fetch_assoc($query)) {
	$row_num++;
	
	if ($row_num == 1)
		$last_auction = $row['auction_id'];
	
	array_push($auctions, $row['auction_id']);
}
// ********** Last Two Auctions (approved) ********** //

// ********** Last Auction (NOT approved) ********** //
$current_auction = 0;
$sql = "SELECT * FROM auctions WHERE auction_ran=1 AND approved=0 AND auction_id > " . $last_auction . " LIMIT 1";
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
if ($num_rows > 0) {
	$row = mysql_fetch_assoc($query);
	array_push($auctions, $row['auction_id']);
	$current_auction = $row['auction_id'];
}
// ********** Last Auction (NOT approved) ********** //


//*************************** TEST ONLY **************************//
//if ($current_auction == 0)
//	$current_auction = 22;
//*************************** TEST ONLY **************************//

	
$row_num = 0;
$total_rows = count($auctions);
$sql_or = "";
for ($x = 0; $x < $total_rows; $x++) {
	if ($row_num < ($total_rows - 1) ) 
		$sql_or = $sql_or . "auction_id=" . $auctions[$x] . " OR ";
	else 
		$sql_or = $sql_or . "auction_id=" . $auctions[$x];
		
	$row_num++;
}

$sql1 = "SELECT user_id, COUNT(user_id) AS total FROM auction_winners WHERE (" . $sql_or . ") GROUP BY user_id";
$query1 = mysql_query($sql1);
$num_rows1 = mysql_num_rows($query1);

$sql2 = "SELECT user_id FROM(" . $sql1 . ") AS alias1 WHERE total > 1";
$query2 = mysql_query($sql2);
$num_rows2 = mysql_num_rows($query2);

$sql3 = "SELECT aw2.user_id, school_name, prize_id, prize_points, prize_name, alias2.user_id, first, last, aw2.auction_id, c.class_grade FROM (" . $sql2 . ") AS alias2 LEFT JOIN auction_winners AS aw2 ON (aw2.user_id=alias2.user_id AND (" . $sql_or . ")) JOIN users AS u ON alias2.user_id=u.user_id JOIN schools USING (school_id) JOIN classes AS c USING (class_id) JOIN prizes_auction USING (prize_id) ORDER BY school_name, last, first, auction_id ";
$query3 = mysql_query($sql3);
$num_rows3 = mysql_num_rows($query3);

$color1 = "blue";
$color2 = "red";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Auction Revision'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var checked = false;
			
			function check_all(chckbx) {
				if (chckbx.checked)
					checked = true;
				else
					checked = false;
					
				var elements = document.getElementById("delete_form").elements;
				
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") 
						elements[cntr].checked = checked;
				} 
				
			}
		
			function submit_form() {
				var post_string = "";
					
				var elements = document.getElementById("delete_form").elements;
					
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") {
							
						if (elements[cntr].checked == true) {
							
							if (elements[cntr].name != "check_global") {
								var info = elements[cntr].name.split("_");
								post_string = post_string + info[1] + ":";
							}
								
						}
							
					}
				} 
				
				post_string = post_string.substr(0, post_string.length - 1);
					
				if (post_string != "") {
					document.getElementById("delete_form").elements["user_prize_ids"].value = post_string;
					document.getElementById("delete_form").submit();
				}
				
			}
		</SCRIPT>
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<div>
				<a href="admin_assign_prize.php">RE-ASSIGN PRIZES</a>
			</div>

			<form method="post" name="delete_form" id="delete_form" action="admin_auction_revision.php" accept-charset="UTF-8">
				<input type="hidden" name="user_prize_ids"  id="user_prize_ids" value="">
				<input type="hidden" name="current_auction" id="current_auction" value="<?=$current_auction;?>">
				
				<H1>
					<?=T_('Auction Revision')?>
					<input type="button" onclick="submit_form();" value="GO">
					<a href="admin_auction_ratio.php?auction_id=<?=$current_auction;?>">Ratios</a>
				</H1>
				
				<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
				
				<table class="pretty_grid">
				
					<tr>
						<th align="left"><input type="checkbox" name="check_global" id="check_global" onclick="check_all(this);"><?=T_('All')?></th>
						<th align="left"><?=T_('School')?></th>
						<th align="left"><?=T_('Name')?></th>
						<th align="left"><?=T_('Grade')?></th>						
						<th align="left"><?=T_('Points')?></th>
						<th align="left"><?=T_('Prize')?></th>
						<th align="left"><?=T_('Auction')?></th>
					</tr>
					
					<? 
						$prev_name = ""; 
						$name_counter = 0;
					?>
					
					<? while ($row = mysql_fetch_assoc($query3)) { ?>
					
						<?
							$name = $row['first'] . $row['last'];
							
							$sql4 = "SELECT user_id FROM auction_winners WHERE auction_id=" . $current_auction . " AND user_id=" . $row['user_id'];
							$query4 = mysql_query($sql4);
							$num_rows4 = mysql_num_rows($query4);
														
							if ($num_rows4 > 0) {								
								if ($prev_name != $name) {
									$name_counter++;								
								}
								
								$remainder = $name_counter % 2;
								if ($remainder == 1)
									$color = $color1;
								else
									$color = $color2;
							}
						?>
						
						<? if ($num_rows4 > 0) { ?>
						<tr style="color:<?=$color;?>;">
							<? if ($row['auction_id'] == $current_auction) { ?>
								<td><input type="checkbox" name="checkbox_<?=$row['user_id'];?>;<?=$row['prize_id'];?>"></td>
							<? } else { ?>
								<td></td>
							<? }?>
							<td><?=$row['school_name'];?></td>
							<td><?=$row['first'];?> <?=$row['last'];?></td>	
							<td><?=$row['class_grade'];?></td>												
							<td><?=$row['prize_points'];?></td>
							<td><?=$row['prize_name'];?></td>
							<td><?=$row['auction_id'];?></td>
						</tr>
						<? } ?>
						
						<?
							$prev_name = $name;
						?>
						
					<? } ?>
				</table>

			</form>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
