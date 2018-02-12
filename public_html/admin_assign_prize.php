<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

$ratio = gri('ratio', -1);

$reassignments = array();

if ($ratio > -1) {

	// ***** Get the last auction that has been ran but not approved ***** //
	$sql = "SELECT auction_id FROM auctions WHERE auction_ran=1 AND approved=0 ORDER BY auction_id DESC LIMIT 1";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$current_auction = $row['auction_id'];
	// ***** Get the last auction that has been ran but not approved ***** //

	$sql_where = " WHERE (auction_id <> " . $current_auction . " ";
	$sql_or = " WHERE (auction_id =" . $current_auction . " ";
	
	// ***** Get the previous two auctions ***** //
	$sql = "SELECT auction_id FROM auctions WHERE auction_id < " . $current_auction . " AND auction_ran=1 AND approved=1 ORDER BY auction_id DESC LIMIT 2";
	$query = mysql_query($sql);
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		if ($row_num == 1) {
			$sql_where = $sql_where . " AND auction_id <> " . $row['auction_id'];
			$sql_or = $sql_or . " OR auction_id=" . $row['auction_id'];
		}
		else {
			$sql_where = $sql_where . " AND auction_id <> " . $row['auction_id'] . ") ";
			$sql_or = $sql_or . " OR auction_id=" . $row['auction_id'] . ") ";
		}
	}
	//echo $sql_where . "<br />";
	// ***** Get the previous two auctions ***** //

	// ********** NUMBER OF STUDENTS PER SCHOOL ********** //
	$student_numbers = array();
	$sql = "SELECT count(*) AS total, school_id FROM users JOIN schools USING (school_id) WHERE users.user_registered > 0 GROUP BY school_id";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$student_numbers[$row['school_id']] = $row['total'];
	}
	// ********** NUMBER OF STUDENTS PER SCHOOL ********** //

	// ********** SCHOOL RATIOS ********** //
	$school_ratios = array();
	$sql = "SELECT school_id, school_name, count(*) AS total FROM auction_winners JOIN users USING (user_id) JOIN schools USING (school_id) WHERE auction_id=" . $current_auction . " AND users.user_registered > 0 GROUP BY school_id ORDER BY school_name";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$school_ratios[$row['school_id']] = round((($row['total'] / $student_numbers[$row['school_id']]) * 100),2);
	}
	// ********** SCHOOL RATIOS ********** //


	// ***** Check the prizes that were deleted ***** //
	$sql1 = "SELECT awd.*, u.first, u.last, pa.prize_name, s.school_name FROM auction_winners_deleted AS awd JOIN users AS u USING (user_id) JOIN prizes_auction AS pa USING (prize_id) JOIN schools AS s ON (u.school_id=s.school_id) WHERE auction_id=" . $current_auction;
	$query1 = mysql_query($sql1);
	$row_number = 0;
	while ($row1 = mysql_fetch_assoc($query1)) {

		$row_number++;
		echo $row_number . ") PRIZE:" . $row1['prize_name'] . " NAME:" . $row1['first'] . " " . $row1['last'] . "<br />";
		
		// ***** Check for a user that bid on the prize ***** //
		$sql2 = "SELECT aup.*, u.school_id, u.first, u.last, s.school_name FROM auction_user_prizes AS aup JOIN users AS u USING (user_id) JOIN schools AS s USING (school_id) WHERE auction_id=" . $row1['auction_id'] . " AND user_id <> " . $row1['user_id'] . " AND prize_id=" . $row1['prize_id'] . " ORDER BY quantity DESC";
		$query2 = mysql_query($sql2);	
		$num_rows2 = mysql_num_rows($query2) - 1;
		// ***** Check for a user that bid on the prize ***** //
		
		// ***** Look for a user that can be awarded a prize ***** //
		$row_num = 0;
		do {
		
			// ***** Check for a user that bid on the prize ***** //
			$break_flag = false;
			while ($row2 = mysql_fetch_assoc($query2)) {
				$row_num++;
				
				// ***** Check to see if there is a ratio for the school ***** //
				if (isset($school_ratios[$row2['school_id']]) && $break_flag == false) {
				
					// ***** Check for a school ratio below the set ratio ***** //
					if ($school_ratios[$row2['school_id']] <= $ratio) {

						// ***** Make sure that the user has not won anything in the last three auctions ***** //
						$sql3 = "SELECT * FROM auction_winners " . $sql_or . " AND user_id=" . $row2['user_id'];
						$query3 = mysql_query($sql3);
						$num_rows3 = mysql_num_rows($query3);
						
						if ($num_rows3 == 0) {
							//echo $row_num . ") USER:" . $row2['user_id'] . " SCHOOL:" . $row2['school_id'] . " RATIO:" . $school_ratios[$row2['school_id']] . " SQL:" . $sql3 . "<br />";					
							
							mq("INSERT INTO auction_winners (auction_id, user_id, prize_id, quantity) VALUES (" . $current_auction . ", " . $row2['user_id'] . ", " . $row1['prize_id'] . ", 1)");
							mq("DELETE FROM auction_winners_deleted WHERE auction_id=" . $current_auction . " AND user_id=" . $row1['user_id'] . " AND prize_id=" . $row1['prize_id']);
						
							$reassignment = new reassignment($row1['prize_name'], $row1['first'], $row1['last'], $row1['school_name'], $row2['first'], $row2['last'], $row2['school_name']);
							array_push($reassignments, $reassignment);
							
							echo "*** RE-ASSIGNED ***<br />";
							
							$break_flag = true;
							
						}
						else {
							echo "ERROR 3 ALREADY WON SOMETHING<br />";
						}
						// ***** Make sure that the user has not won anything in the last three auctions ***** //
						
					}
					// ***** Check for a school ratio below the set ratio ***** //
					else {
						echo "ERROR 2 RATIO NOT BELOW RATIO<br />";
					}
					
				}
				else {
					echo "ERROR 1 NO RATIO FOR SCHOOL<br />";
				}
				// ***** Check to see if there is a ratio for the school ***** //
				
			}
			// ***** Check for a user that bid on the prize ***** //
			
		} while ($row_num < $num_rows2);
		// ***** Look for a user that can be awarded a prize ***** //
		
	}
	// ***** Check the prizes that were deleted ***** //
	
}

class reassignment {
	var $prize_name;
	var $name1;
	var $name2;
	var $school1;
	var $school2;
	
	function reassignment($prize_name, $first1, $last1, $school1, $first2, $last2, $school2) {
		$this->prize_name = $prize_name;
		$this->name1 = $first1 . " " . $last1;
		$this->name2 = $first2 . " " . $last2;
		$this->school1 = $school1;
		$this->school2 = $school2;
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Assign Prize'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
	</HEAD>
	
	<BODY>
		
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<form method="post" action="admin_assign_prize.php"  accept-charset="UTF-8">
				<label>Ratio:<input type="text" maxlength="2" name="ratio"></label>
				<input type="submit" value="GO">
			</form>
			
			<br />
			
			<table class="pretty_grid">
				<tr>
					<th align="left"><?=T_('Prize')?></th>
					<th align="left"><?=T_('From')?></th>
					<th align="left"></th>
					<th align="left"><?=T_('To')?></th>
				</tr>			
				
				<? for ($cntr = 0; $cntr < count($reassignments); $cntr++) { ?>
				<tr>
					<td><?=$reassignments[$cntr]->prize_name;?></td>
					<td><?=$reassignments[$cntr]->name1;?> - <?=$reassignments[$cntr]->school1;?></td>
					<td></td>
					<td><?=$reassignments[$cntr]->name2;?> - <?=$reassignments[$cntr]->school2;?></td>
				</tr>
				<? } ?>
			</table>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
