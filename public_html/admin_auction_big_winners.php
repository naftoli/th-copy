<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

if ($admin_user['auth'] != 'super') {
	header("Location:admin.php");
}

$action = gr("action", "");

if ($action != "") {
	$info = gr("info");
	
	$info1 = split(":", $info);
	for ($cntr1 = 0; $cntr1 < count($info1); $cntr1++) {
		$info2 = split(";", $info1[$cntr1]);
		$sql = "DELETE FROM auction_winners WHERE auction_id=" . $info2[0] . " AND user_id=" . $info2[1] . " AND prize_id=" . $info2[2];
		mq($sql);
	}
	
}

$sql = "SELECT aw.auction_id, aw.user_id, aw.prize_id, u.first, u.last, pa.prize_name, pa.prize_points, pa2.prize_name AS last_prize, pa2.prize_points AS last_points, pa3.prize_name AS last_prize_2, pa3.prize_points AS last_points_2, pa4.prize_name AS last_prize_3, pa4.prize_points AS last_points_3 FROM auction_winners AS aw JOIN users AS u USING (user_id) JOIN prizes_auction AS pa USING (prize_id) LEFT JOIN auction_winners AS aw2 ON (aw2.auction_id=23 AND aw2.user_id=aw.user_id) LEFT JOIN prizes_auction AS pa2 ON (aw2.prize_id=pa2.prize_id) LEFT JOIN auction_winners AS aw3 ON (aw3.auction_id=22 AND aw3.user_id=aw.user_id) LEFT JOIN prizes_auction AS pa3 ON (aw3.prize_id=pa3.prize_id) LEFT JOIN auction_winners AS aw4 ON (aw4.auction_id=15 AND aw4.user_id=aw.user_id) LEFT JOIN prizes_auction AS pa4 ON (aw4.prize_id=pa4.prize_id) WHERE aw.auction_id=24 AND pa.prize_points > 0 ORDER BY pa.prize_name";
$query = mq($sql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Auction Revision'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			function get_data() {
				var info = "";
				
				var form = document.getElementById("big_winners_form");
				var inputs = form.getElementsByTagName("input");
				
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						if (inputs[cntr].checked == true) {
							var id = inputs[cntr].getAttribute("id");
							info = info + id + ":";
						}
					}
				}
				
				if (info.length > 0) {
					info = info.substr(0, info.length - 1);
					document.getElementById("info").value = info;
					document.getElementById("action").value = "delete";
					alert(document.getElementById("info").value);
					document.getElementById("big_winners_form").submit();
				}
			}
		</SCRIPT>
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<FORM method="post" name="big_winners_form" id="big_winners_form" action="admin_auction_big_winners.php" accept-charset="UTF-8">
				<input type="hidden" id="info" name="info">
				<input type="hidden" id="action" name="action">
				
				<input type="button" onclick="get_data();" VALUE="DELETE">
				
				<TABLE class="pretty_grid">			
					<TR>
						<TH>NAME</TH>
						<TH>PRIZE</TH>
						<TH>DELETE</TH>
						<TH>AUCTION 23</TH>
						<TH>AUCTION 22</TH>
						<TH>AUCTION 15</TH>
					</TR>
					
					<? while ($row = mysql_fetch_assoc($query)) : ?>
						<TR>
							<TD><?=$row['first'] . " " . $row['last'];?></TD>
							<TD><?=$row['prize_name'] . " " . $row['prize_points'];?></TD>
							<TD><input type="checkbox" id="<?=$row['auction_id'];?>;<?=$row['user_id'];?>;<?=$row['prize_id'];?>" /></TD>
							<TD><?=$row['last_prize'];?> <?=$row['last_points'];?></TD>
							<TD><?=$row['last_prize_2'];?> <?=$row['last_points_2'];?></TD>	
							<TD><?=$row['last_prize_3'];?> <?=$row['last_points_3'];?></TD>								
						</TR>
					<? endwhile; ?>
				</TABLE>

			</FORM>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
