<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

session_start();
if (!isset($_SESSION['added']))
	$_SESSION['added'] = 0;

// ***** Get the last auction that has been ran but not approved ***** //
//$sql = "SELECT auction_id FROM auctions WHERE auction_ran=1 AND approved=0 ORDER BY auction_id DESC LIMIT 1";
//$query = mysql_query($sql);	
//$row = mysql_fetch_assoc($query);
//$auction_id = $row['auction_id'];
//echo "AUCTION ID:" . $auction_id . "<br />";
$auction_id = 24;
// ***** Get the last auction that has been ran but not approved ***** //
	
$action = gr('action', '');
$prize_id = gri('prize_id', -1);
//echo "PRIZE ID:" . $prize_id . "<br />";

if ($action == "") {


	if ($prize_id == -1) {
		$sql = "SELECT ap.prize_id, pa.prize_name, pa.prize_points FROM auction_prizes AS ap JOIN prizes_auction AS pa USING (prize_id) WHERE auction_id=" . $auction_id . " ORDER BY prize_points";
		$query = mysql_query($sql);
	}
	else {
		$users = array();
		
		//$sql_where = " WHERE (auction_id <> " . $auction_id . " ";
		$sql_or = " WHERE (auction_id =" . $auction_id . " ";
			
		// ***** Get the previous two auctions ***** //
		$sql = "SELECT auction_id FROM auctions WHERE auction_id < " . $auction_id . " AND auction_ran=1 AND approved=1 ORDER BY auction_id DESC LIMIT 2";
		$query = mysql_query($sql);
		$row_num = 0;
		while ($row = mysql_fetch_assoc($query)) {
			$row_num++;
			
			if ($row_num == 1) {
				//$sql_where = $sql_where . " AND auction_id <> " . $row['auction_id'];
				$sql_or = $sql_or . " OR auction_id=" . $row['auction_id'];
			}
			else {
				//$sql_where = $sql_where . " AND auction_id <> " . $row['auction_id'] . ") ";
				$sql_or = $sql_or . " OR auction_id=" . $row['auction_id'] . ") ";
			}
		}
		// ***** Get the previous two auctions ***** //
		
		// ***** Check for a user that bid on the prize ***** //
		$sql1 = "SELECT aup.user_id, u.first, u.last, s.school_name FROM auction_user_prizes AS aup JOIN users AS u USING (user_id) JOIN schools AS s USING (school_id) WHERE auction_id=" . $auction_id . " AND prize_id=" . $prize_id;	
		$query1 = mysql_query($sql1);
		
		while ($row1 = mysql_fetch_assoc($query1)) {
		
			// ***** and did not win anything from this auction *****//
			$sql2 = "SELECT user_id FROM auction_winners " . $sql_or .  " AND user_id=" . $row1['user_id'];
			$query2 = mysql_query($sql2);
			$num_rows = mysql_num_rows($query2);
			if ($num_rows == 0) {
				$user = new user($row1);
				array_push($users, $user);
			}
			//else {
			//	echo "ALREADY WON A PRIZE<br />";
			//}
			
		}
		
		$sql2 = "SELECT prize_name, prize_points FROM prizes_auction WHERE prize_id=" . $prize_id;
		$query2 = mysql_query($sql2);
		$row2 = mysql_fetch_assoc($query2);
		$prize_name = $row2['prize_name'];
		$prize_points = $row2['prize_points'];
		
		$sql = "SELECT ap.prize_id, pa.prize_name, pa.prize_points FROM auction_prizes AS ap JOIN prizes_auction AS pa USING (prize_id) WHERE auction_id=" . $auction_id . " ORDER BY prize_points";
		$query = mysql_query($sql);
		
	}

}
else {

	$ids = gr('user_ids');
	$user_ids = explode(":", $ids);
	
	for ($cntr = 0; $cntr < count($user_ids); $cntr++) {
			
		$sql = "INSERT INTO auction_winners (auction_id, user_id, prize_id, quantity) VALUES (" . $auction_id . ", " . $user_ids[$cntr] . ", " . $prize_id . ", 1)";
		
		//echo $sql . "<br />";
		mq($sql);

		$_SESSION['added']++;
	}
	
	$prize_id = -1;
	$sql = "SELECT ap.prize_id, pa.prize_name, pa.prize_points FROM auction_prizes AS ap JOIN prizes_auction AS pa USING (prize_id) WHERE auction_id=" . $auction_id . " ORDER BY prize_points";
	$query = mysql_query($sql);

}


class user {
	var $user_id;
	var $first;
	var $last;
	var $school_name;
	
	function user($row) {
		$this->user_id = $row['user_id'];
		$this->first = $row['first'];
		$this->last = $row['last'];
		$this->school_name = $row['school_name'];
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Assign New Prize'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var checked = false;
			
			function check_all(chckbx) {
				if (chckbx.checked)
					checked = true;
				else
					checked = false;
					
				var elements = document.getElementById("assign_new_prize_form").elements;
				
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") 
						elements[cntr].checked = checked;
				} 
				
			}
			
			function submit_form() {
				var post_string = "";
					
				var elements = document.getElementById("assign_new_prize_form").elements;
					
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") {
							
						if (elements[cntr].checked == true) {
							
							if (elements[cntr].name != "check_global") {
								post_string = post_string + elements[cntr].value + ":";
							}
								
						}
							
					}
				} 
				
				post_string = post_string.substr(0, post_string.length - 1);
				
				if (post_string != "") {
					document.getElementById("assign_new_prize_form").elements["user_ids"].value = post_string;
					document.getElementById("assign_new_prize_form").submit();
				}
				
			}			
		</SCRIPT>
	</HEAD>
	
	<BODY>
		
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<div>
				<h1>
					Added:<?=$_SESSION['added'];?>
				</h1>
			</div>
			
			<? if ($prize_id == -1) { ?>
			
				<form method="post" action="admin_assign_new_prize.php"  accept-charset="UTF-8">
					<label>
						Prize:
						<select name="prize_id">
							<option value="-1"><?=T_('Select a Prize');?></option>
							<? while ($row = mysql_fetch_assoc($query)) { ?>
							<option value="<?=$row['prize_id'];?>"><?=$row['prize_name'];?></option>
							<? } ?>
						</select>
					</label>
					<input type="submit" value="GO">
				</form>
			
			<? } else { ?> <!-- if ($prize_id == -1) -->
			
				<form method="post" name="assign_new_prize_form" id="assign_new_prize_form" action="admin_assign_new_prize.php" accept-charset="UTF-8">
								
					<input type="hidden" name="prize_id"  id="prize_id" value="<?=$prize_id;?>">
					<input type="hidden" name="user_ids"  id="user_prize_ids" value="">
					<input type="hidden" name="action"  id="action" value="assign">
					
					<br />
					
					<h2>
						Prize:<?=$prize_name;?> Points:<?=$prize_points;?>
					</h2>
					
					<br />
					
					<input type="button" onclick="submit_form();" value="GO">
					
					<br />
					
					<table class="pretty_grid">
						<tr>
							<th align="left">&nbsp;</th>
							<th align="left"><?=T_('Name');?></th>
							<th align="left"><?=T_('School');?></th>
							<th align="left"><input type="checkbox" name="check_global" id="check_global" onclick="check_all(this);"></th>
						</tr>
						
						<? for ($cntr= 0; $cntr < count($users); $cntr++) { ?>
						<tr>
							<td><?=($cntr+1);?></td>
							<td><?=$users[$cntr]->first;?> <?=$users[$cntr]->last;?></td>
							<td><?=$users[$cntr]->school_name;?></td>
							<td><input type="checkbox" value="<?=$users[$cntr]->user_id;?>"></td>
						</tr>
						<? } ?>
						
					</table>
					
					<br />
					
					<label>
						Prize:
						<select name="prize_id" onchange="document.getElementById('action').value=''; document.getElementById('assign_new_prize_form').submit();">
							<? while ($row = mysql_fetch_assoc($query)) { ?>
							
							<? if ($row['prize_id'] == $prize_id) { ?>
								<option selected value="<?=$row['prize_id'];?>"><?=$row['prize_name'];?></option>
							<? } else { ?>
								<option value="<?=$row['prize_id'];?>"><?=$row['prize_name'];?></option>
							<? } ?>
							
							<? } ?>
						</select>
					</label>
					
				
				</form>
			
			<? } ?> <!-- if ($prize_id == -1) -->
			
		</DIV>		
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
