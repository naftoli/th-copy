<? 
$admin_auth = array('school'); 

require('header.php'); 
require_once('calendar.php'); 
$action = gr('action');
$auction_id = gri('auction_id', -1);
assure_id_school('school_id');
$school_id = gri('school_id', -2);
$no_of_winners = 0;

class qtyArray {
  public $length = 0;
  private $store = array();

  public function get($pos) {
    if($pos >= $this->length) return NULL;
    foreach($this->store as $val) {
      if(($pos -= $val['qty']) < 0) return $val['data'];
    }
  }

  public function append($data, $qty=1) {
    $this->store[] = array('qty'=>$qty, 'data'=>$data);
    $this->length += $qty;
    return $this->length;
  }

  public function shift($count) {
    foreach($this->store as &$val) {
      $count -= $val['qty'];
      $this->length -= $val['qty'];
      $val['qty'] = 0;
      if($count < 0) {
        $val['qty'] -= $count;
        $this->length -= $count;
        break;
      }
    }
    return $this->length;
  }
}

if (!empty($action)) {

	$prizes = mq("SELECT prize_id, prizes_auction.prize_name, prizes_auction.prize_ratio, auction_prizes.available, SUM(auction_user_prizes.quantity) quantity, COUNT(DISTINCT user_id) num_users FROM auction_user_prizes JOIN auctions USING (auction_id) JOIN auction_prizes USING (auction_id, prize_id) JOIN prizes_auction USING (prize_id) JOIN users USING (user_id) WHERE auction_id = $auction_id AND auctions.auction_ran = 0 AND " . ($admin_user['auth'] == 'super' && $school_id == -1 ? ' auctions.school_id IS NULL' : " auctions.school_id = $school_id AND users.school_id = $school_id") . " AND (prizes_auction.school_id = users.school_id OR prizes_auction.school_id IS NULL) GROUP BY prize_id, prizes_auction.prize_name, prizes_auction.prize_ratio ORDER BY prizes_auction.prize_number, prizes_auction.prize_id");
	switch($action) {
	
		case 'run':
			while($prizes_row = mysql_fetch_assoc($prizes)) {
				$result = mq("SELECT school_id, user_id, quantity FROM auction_user_prizes JOIN users USING (user_id) WHERE auction_id = $auction_id AND prize_id = {$prizes_row['prize_id']}" . ($admin_user['auth'] == 'super' && $school_id == -1 ? '' : " AND users.school_id = $school_id") . ' ORDER BY school_id, user_id');

				$num_prizes = is_null($prizes_row['available']) ? ceil($prizes_row['quantity']/$prizes_row['prize_ratio']) : $prizes_row['available'];

				$tickets = array();
				while($row = mysql_fetch_assoc($result)) {
					if (!isset($tickets[$row['school_id']])) 
						$tickets[$row['school_id']] = new qtyArray();
					$tickets[$row['school_id']]->append($row['user_id'], $row['quantity']*$num_prizes);
				}

				foreach($tickets as &$entries) {
					while($entries->length >= $prizes_row['quantity']) {
						mq("INSERT INTO auction_winners (auction_id, user_id, prize_id, quantity) VALUES ($auction_id, " . $entries->get(mt_rand(0, $prizes_row['quantity']-1)) . ", {$prizes_row['prize_id']}, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
						$entries->shift($prizes_row['quantity']);
					}
				}

				$ticket_down = 0;
				foreach($tickets as &$entries) {
					while($entries->length) {
						if (!$ticket_down) {
							$winner = mt_rand(0, $prizes_row['quantity']-1);
							$ticket_down = $prizes_row['quantity'];
						}

						if ($winner >= 0 && $entries->length > $winner) {
							mq("INSERT INTO auction_winners (auction_id, user_id, prize_id, quantity) VALUES ($auction_id, " . $entries->get($winner) . ", {$prizes_row['prize_id']}, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
						}
						
						$winner -= $entries->length;

						if ($ticket_down <= $entries->length) {
							$entries->shift($ticket_down);
							$ticket_down = 0;
						} 
						else {
							$ticket_down -= $entries->length;
							$entries->shift($entries->length);
						}
					}
			
				}

			}

			$sql = "UPDATE auctions SET auction_ran = 1 WHERE auction_id=" . $auction_id;
			$query = mysql_query($sql);
			
			$sql = "SELECT aw.*, users.*, school_name, school_number, classes.class_grade, classes.class_sub, prizes_auction.prize_name, prize_number ";
			$sql = $sql . "FROM auction_winners AS aw ";
			$sql = $sql . "JOIN users USING (user_id) ";
			$sql = $sql . "JOIN prizes_auction USING (prize_id) ";
			$sql = $sql . "JOIN auctions USING (auction_id) ";
			$sql = $sql . "LEFT JOIN schools ON (users.school_id=schools.school_id) ";
			$sql = $sql . "LEFT JOIN classes USING (class_id) ";
			$sql = $sql . "WHERE auction_id=" . $auction_id . " ";
			$sql = $sql . "AND auctions.auction_ran=1 ";
			if ($school_id > 0)
				$sql = $sql . "AND school_id=" . $school_id . " ";
			$sql = $sql . "ORDER BY users.last "; //prizes_auction.prize_number, auction_winners.prize_id, school_name, classes.class_grade, classes.class_sub, class_id, users.last, users.first, auction_winners.auction_id ";			
			$winners = mysql_query($sql);			
		break;

		case 'view':
			break;

		default:
			user_error('unknown action', E_USER_ERROR);
			break;
	}
	
}

$auction_sql = "SELECT * FROM auctions WHERE auction_ran=0 ";
if ($school_id > 0)
	$auction_sql = $auction_sql . " AND school_id=" . $school_id . " "; 
else
	$auction_sql = $auction_sql . " AND school_id IS NULL "; 
$auction_sql = $auction_sql . "ORDER BY auction_date DESC"; 
$auction_query = mysql_query($auction_sql);

if ($auction_id > 0) {
	$sql = "SELECT aw.*, users.*, school_name, school_number, classes.class_grade, classes.class_sub, prizes_auction.prize_name, prize_number ";
	$sql = $sql . "FROM auction_winners AS aw ";
	$sql = $sql . "JOIN users USING (user_id) ";
	$sql = $sql . "JOIN prizes_auction USING (prize_id) ";
	$sql = $sql . "JOIN auctions USING (auction_id) ";
	$sql = $sql . "LEFT JOIN schools ON (users.school_id=schools.school_id) ";
	$sql = $sql . "LEFT JOIN classes USING (class_id) ";
	$sql = $sql . "WHERE auction_id=" . $auction_id . " ";
	$sql = $sql . "AND auctions.auction_ran=1 ";
	if ($school_id > 0)
		$sql = $sql . "AND school_id=" . $school_id . " ";
	$sql = $sql . "ORDER BY users.last "; //prizes_auction.prize_number, auction_winners.prize_id, school_name, classes.class_grade, classes.class_sub, class_id, users.last, users.first, auction_winners.auction_id ";			
	$winners = mysql_query($sql);
	$no_of_winners = mysql_num_rows($winners);
	
	$non_winners_sql = "SELECT u.*, s.school_name ";
	$non_winners_sql = $non_winners_sql . "FROM auction_user_prizes AS aup ";
	$non_winners_sql = $non_winners_sql . "JOIN users AS u ON (aup.user_id=u.user_id) ";
	$non_winners_sql = $non_winners_sql . "JOIN schools AS s ON (u.school_id=s.school_id) ";
	$non_winners_sql = $non_winners_sql . "LEFT JOIN auction_winners AS aw ON (aup.user_id=aw.user_id) ";
	$non_winners_sql = $non_winners_sql . "WHERE aup.auction_id=" . $auction_id . " AND aw.auction_id IS NULL ";
	$non_winners_sql = $non_winners_sql . "GROUP BY aup.user_id";
	$non_winners_query = mysql_query($non_winners_sql);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Run Chinese Auction'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<script>
			var quantity_input = "";
			
			function edit_quantity(edit_button, auction_id, user_id, prize_id) {			
				$(edit_button).css('visibility', 'hidden');
				$(edit_button).parents("tr:first").find("span[name=quantity_span]").css('visibility', 'hidden');
				$(edit_button).parents("tr:first").find("input[name=quantity]").css('visibility', 'visible');
				$(edit_button).parents("tr:first").find("input[name=save_button]").css('visibility', 'visible');

			}
			
			function save_quantity(save_button, auction_id, user_id, prize_id) {
				var quantity = $(save_button).parents("tr:first").find("input[name=quantity]").val();
				
				var function_name = "update_winner_quantity";
				var parameters = [auction_id, user_id, prize_id, quantity];
				var url = "edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;					
					
				$.getJSON(url, function(success) {					
					if (success == 1) {
						$(save_button).parents("tr:first").find("span[name=quantity_span]").html(quantity);
						$(save_button).css('visibility', 'hidden');
						$(save_button).parents("tr:first").find("input[name=quantity]").css('visibility', 'hidden');
						$(save_button).parents("tr:first").find("span[name=quantity_span]").css('visibility', 'visible');
						$(save_button).parents("tr:first").find("input[name=edit_button]").css('visibility', 'visible');				
					}
				});
			}
			
			function reassign_prize(reassign_button, auction_id, user_id, prize_id) {
				quantity_input = $(reassign_button).parents("tr:first").find("input[name=quantity]");
				var quantity = $(reassign_button).parents("tr:first").find("input[name=quantity]").val();
				
				$('#wrapper').fadeTo("slow", 0.3);	
				$('#non_winners_overlay').css('visibility', 'visible');
				
				document.getElementById("hidden_auction_id").value = auction_id;
				document.getElementById("hidden_user_id").value = user_id;
				document.getElementById("hidden_prize_id").value = prize_id;
				document.getElementById("hidden_quantity").value = quantity;				
			}
			
			function assign(assign_button, user_id) {
				var auction_id = document.getElementById("hidden_auction_id").value;
				var hidden_user_id = document.getElementById("hidden_user_id").value;
				var hidden_prize_id = document.getElementById("hidden_prize_id").value;
				var hidden_quantity = document.getElementById("hidden_quantity").value;	
				
				if (hidden_quantity > 1) {
					var function_name = "assign_winner";
					var parameters = [auction_id, user_id, hidden_user_id, hidden_prize_id, hidden_quantity];
					var url = "add_functions.php?function_name=" + function_name + "&parameters=" + parameters;					
					
					$.getJSON(url, function(success) {					
						if (success == 1) {
							document.getElementById("hidden_quantity").value = hidden_quantity - 1;
							$(assign_button).parents("tr:first").remove();
							$(quantity_input).val(document.getElementById("hidden_quantity").value);
							alert(document.getElementById("hidden_quantity").value + " left");								
						}
					});				
				}
				else {
					alert("Only 1 left");
				}
				
				
			}
			
			function close_overlay() {		
				$('#wrapper').fadeTo("slow", 1);	
				$('#non_winners_overlay').css('visibility', 'hidden');
			}
		</script>
	</HEAD>
	
	<BODY>

		<? include('admin_header.php'); ?>

		<input type="hidden" name="hidden_auction_id" id="hidden_auction_id" value="">
		<input type="hidden" name="hidden_user_id" id="hidden_user_id" value="">
		<input type="hidden" name="hidden_prize_id" id="hidden_prize_id" value="">
		<input type="hidden" name="hidden_quantity" id="hidden_quantity" value="">
		
		<DIV CLASS="body">
			
			<H1>
				<?=T_('Run Chinese Auction')?>
			</H1>
			
			<? if(!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?> <!-- if(!empty($message)) : -->
			
<? if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : ?>

	<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
	
			<FORM action="admin_auction_run.php" method="get" accept-charset="UTF-8">
				<P>
					<?=T_('Select Institution')?>: 
					<SELECT name="school_id">
						<? if($admin_user['auth'] == 'super') : ?>
						<OPTION value="-1">&lt;<?=T_('Multi institution auctions')?>&gt;
						<?endif;?>
						<?while($school_row = mysql_fetch_assoc($school_result)):?>
						<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
						<?endwhile;?>
					</SELECT> 
					<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
				</P>
			</FORM>
			
			<HR>
<? endif; ?> <!-- if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : -->

<? if ($school_id == -2) : ?>
	<?=T_('Please select an Institution.')?>
<?else:?>
			<FORM action="admin_auction_run.php" method="get" accept-charset="UTF-8">
				<P>
					<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
					<INPUT type="hidden" name="action" value="view">

					<?=T_('Auction')?>: 
					
					<!-- ********** AUCTION ID ********** -->
					<SELECT name="auction_id" dir="rtl">
						<? $multi = false; ?>
						<? while($auction_row = mysql_fetch_assoc($auction_query)): ?>
						<OPTION value="<?=$auction_row['auction_id']?>" <?=$auction_row['auction_id'] == $auction_id ? 'SELECTED' : ''?>><?if(is_null($auction_row['school_id'])) { $multi = true; echo '* '; }?><?=es(dateToHebrew($auction_row['auction_date']))?></OPTION>
						<?endwhile;?>
					</SELECT> 
					<!-- ********** AUCTION ID ********** -->
					
					<?= $multi ? '*' . T_('Multi schoool auction') : ''?>
					
					<BR>
					
					<INPUT class="submit" type="submit" value="<?=T_('View entries')?>">
				</P>
			</FORM>
			
		<? if ($no_of_winners > 0) : ?>

		<TABLE class="pretty_grid">
				<CAPTION>
					<?=T_('Winners')?>
				</CAPTION>
				
				<TR>
					<TH><?=T_('School #')?></TH>
					<TH><?=T_('School Name')?></TH>
					<TH><?=T_('Grade')?></TH>
					<TH><?=T_('Name')?></TH>
					<TH><?=T_('Prize #')?></TH>
					<TH><?=T_('Prize')?></TH>
					<TH><?=T_('REG');?></TH>
					<TH><?=T_('Quantity')?></TH>					
					<TH></TH>
				</TR>
				
				<?while($row = mysql_fetch_assoc($winners)):?>	
				<TR>
					<TD><?=$row['school_number']?></TD>
					<TD><?=es($row['school_name'])?></TD>
					<TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
					<TD><?=es($row['first'])?> <?=es($row['last'])?></TD>
					<TD><?=$row['prize_number']?></TD>
					<TD><?=es($row['prize_name'])?></TD>
					
					<? if ($row['user_registered'] > 0) : ?>
					<TD>y</TD>
					<? else : ?>
					<TD>n</TD>
					<? endif; ?>
					
					<TD>
						<span name="quantity_span" id="quantity_span"><?=$row['quantity']?></span>
						<input type="button" name="reassign_button" id="reassign_button" value="REASSIGN" onclick="reassign_prize(this, <?=$auction_id;?>, <?=$row['user_id'];?>, <?=$row['prize_id'];?>);">
						<input type="text" name="quantity" id="quantity" value="<?=$row['quantity']?>" style="visibility:hidden;">
					</TD>
					<TD>
						<input type="button" name="edit_button" id="edit_button" value="EDIT" onclick="edit_quantity(this, <?=$auction_id;?>, <?=$row['user_id'];?>, <?=$row['prize_id'];?>);">
						<input type="button" id="save_button" name="save_button" style="visibility:hidden;" value="SAVE" onclick="save_quantity(this, <?=$auction_id;?>, <?=$row['user_id'];?>, <?=$row['prize_id'];?>);">
					</TD>						
				</TR>
				<?endwhile;?>			
			</TABLE>
		<? elseif(isset($prizes)) : ?>

<?
	$sql = "SELECT auction_date, auction_points_start_date, auction_points_trigger_date FROM auctions WHERE auction_id=" . $auction_id;
	$auction_row = mysql_fetch_assoc(mq("SELECT auction_date, auction_points_start_date, auction_points_trigger_date FROM auctions WHERE auction_id = $auction_id"));
	$result = mq("SELECT user_id, SUM(prize_points*quantity) points FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = $auction_id GROUP BY user_id");
	$users_over = array();
	while($row = mysql_fetch_assoc($result)) {
	  $auction_points = auctionPoints($row['user_id'], $auction_row);
	  if($row['points'] > $auction_points['cur']) $users_over[$row['user_id']] = array('used' => $row['points'], 'available' => $auction_points['cur']);
	}
	
	if ($users_over) :
	  $result = mq('SELECT user_id, school_name, school_id, first, last FROM users LEFT JOIN schools USING (school_id) WHERE user_id IN (' . implode(',', array_keys($users_over)) . ') ORDER BY school_name, last, first');
?>

			<TABLE class="pretty_grid">
				<CAPTION style="color: red; font-size: 200%;">
					<?=T_('Users with too many tickets')?>
				</CAPTION>
				
				<TR>
					<TH><?=T_('School')?></TH>
					<TH><?=T_('Name')?></TH>
					<TH><?=T_('Edit')?></TH>
					<TH><?=T_('Used Points')?></TH>
					<TH><?=T_('Available Points')?></TH>
					<TH><?=T_('Overage')?></TH>
				</TR>
				
				<?while($row = mysql_fetch_assoc($result)):?>
				<TR>
					<TD><?=es($row['school_name'])?></TD>
					<TD><?=es($row['first'] . ' ' . $row['last'])?></TD>
					<TD><A HREF="admin_user_auction.php?school_id=<?=$row['school_id']?>&amp;auction_id=<?=$auction_id?>&amp;user_id=<?=$row['user_id']?>"><?=T_('Edit auction entries')?></A>
					<TD><?=$users_over[$row['user_id']]['used']?></TD>
					<TD><?=$users_over[$row['user_id']]['available']?></TD>
					<TD><?=$users_over[$row['user_id']]['used'] - $users_over[$row['user_id']]['available']?></TD>
				</TR>
				<?endwhile;?>
			</TABLE>
	<? endif; ?> <!-- if ($school_id == -2) : -->
	
			<HR> 
			
			<TABLE class="pretty_grid">
				<TR>
					<TH><?=T_('Name')?></TH>
					<TH STYLE="text-align: right;"><?=T_('Tickets')?></TH>
					<TH STYLE="text-align: right;"><?=T_('Distinct Users')?></TH>
					<TH STYLE="text-align: right;"><?=T_('Ratio')?></TH>
					<TH STYLE="text-align: right;"><?=T_('Prizes to award')?></TH>
				</TR>
				
			<?while($prizes_row = mysql_fetch_assoc($prizes)):?>
				<TR class="bold double2">
					<TD><?=es($prizes_row['prize_name'])?></TD>
					<TD STYLE="text-align: right;"><?=$prizes_row['quantity']?></TD>
					<TD STYLE="text-align: right;"><?=$prizes_row['num_users']?></TD>
					<TD STYLE="text-align: right;"><?=is_null($prizes_row['available']) ? $prizes_row['prize_ratio'] : 'N/A'?></TD>
					<TD STYLE="text-align: right;"><?=is_null($prizes_row['available']) ? ceil($prizes_row['quantity']/$prizes_row['prize_ratio']) : $prizes_row['available']?></TD>
				</TR>
				
				<? $first = true; ?>
				<? $entries = mq("SELECT school_name, IFNULL(schools.school_id, -1) school_id, SUM(auction_user_prizes.quantity) quantity, COUNT(DISTINCT user_id) num_users FROM auction_user_prizes JOIN auctions USING (auction_id) JOIN auction_prizes USING (auction_id, prize_id) JOIN prizes_auction USING (prize_id) JOIN users USING (user_id) LEFT JOIN schools ON (users.school_id = schools.school_id) WHERE auction_id = $auction_id AND " . ($admin_user['auth'] == 'super' && $school_id == -1 ? ' auctions.school_id IS NULL' : " auctions.school_id = $school_id") . " AND (prizes_auction.school_id = users.school_id OR prizes_auction.school_id IS NULL) AND prize_id = {$prizes_row['prize_id']} GROUP BY schools.school_id, school_name ORDER BY school_name"); ?>
				<?while($row = mysql_fetch_assoc($entries)):?>
				<TR>
					<TD>
						<A HREF="#" onClick="if(this.innerHTML == '+') { this.innerHTML='&minus;'; document.getElementById('school_<?=$prizes_row['prize_id']?>_<?=$row['school_id']?>').style.display = ''; } else { this.innerHTML='+'; document.getElementById('school_<?=$prizes_row['prize_id']?>_<?=$row['school_id']?>').style.display = 'none'; } return false; ">+</A>&nbsp;&rarr;&nbsp;<?=es($row['school_name'])?>
						<DIV style="display: none; border: 1px solid black; margin: 4px;" id="school_<?=$prizes_row['prize_id']?>_<?=$row['school_id']?>">
							<? $result = mq("SELECT class_grade, class_sub, user_id, first, last, quantity FROM users JOIN auction_user_prizes USING (user_id) LEFT JOIN classes USING (school_id, class_id) WHERE auction_id = $auction_id AND prize_id = {$prizes_row['prize_id']} AND school_id = {$row['school_id']} ORDER BY class_grade, class_sub, last, first"); ?>
							<?while($names = mysql_fetch_assoc($result)):?>
							<A HREF="admin_user_auction.php?school_id=<?=$row['school_id']?>&amp;auction_id=<?=$auction_id?>&amp;action=save&amp;user_id=<?=$names['user_id']?>"><?=$names['quantity'], '&#215; ', es($names['class_grade']), '-', es($names['class_sub']), ': ', es($names['last']), ', ', es($names['first'])?></A><BR>
							<?endwhile;?>
						</DIV>
					</TD>
					<TD STYLE="text-align: right; vertical-align: top;"><?=$row['quantity']?></TD>
					<TD STYLE="text-align: right; vertical-align: top;"><?=$row['num_users']?></TD>
					
					<?if($first): $first = false;?>
					<TD colspan="2" rowspan="<?=mysql_num_rows($entries)?>"></TD>
					<?endif;?>
					
				</TR>
				<?endwhile;?>
				
			<?endwhile;?>
			
			</TABLE>
		
			<FORM action="admin_auction_run.php" method="post" accept-charset="UTF-8">
				<P>
					<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
					<INPUT type="hidden" name="auction_id" value="<?=$auction_id?>">
					<INPUT type="hidden" name="action" value="run">
					<INPUT class="submit" type="submit" value="<?=T_('Run auction')?>">
				</P>
			</FORM>
			
	<?endif;?> <!-- if ($users_over) : -->
	
	<?endif;?> <!-- if (isset($winners)) : -->
	
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
		<div style="position:absolute; top:10px; left:200px; margin:auto; visibility:hidden;" name="non_winners_overlay" id="non_winners_overlay">
			
			<TABLE style="border:1px solid; background-color:#00ff00;">
				<TR>
					<TD><input type="button" value="CLOSE" onclick="close_overlay();"></TD>
				</TR>
				
				<TR>
					<TH>First</TH>
					<TH>Last</TH>
					<TH>School</TH>
					<TH></TH>
				</TR>
				
				<? while ($row = mysql_fetch_assoc($non_winners_query)) : ?>
				<TR>
					<TD><?=$row['first'];?></TD>
					<TD><?=$row['last'];?></TD>
					<TD><?=$row['school_name'];?></TD>
					<TD><input name="assign_button" id="assign_button" type="button" value="ASSIGN" onclick="assign(this, <?=$row['user_id'];?>);"></TD>
				</TR>
				<? endwhile; ?>
			</TABLE>
		</div>
		
	</BODY>
	
</HTML>
