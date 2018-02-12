<?
$admin_auth = array('school'); 
require('header.php');  

$auction_id = $_GET['auction_id'];
$school_id = isset( $_GET['school_id'] ) ? $_GET['school_id'] : 0;
		
include ("classes/auction_school.php");
include ("classes/auction_winner.php");

$sql = "SELECT SUM(quantity) AS total_tickets FROM auction_user_prizes WHERE auction_id=" . $auction_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$total_tickets = $row['total_tickets'];

$sql = "SELECT SUM(available) AS total_prizes FROM auction_prizes WHERE auction_id=" . $auction_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$total_prizes = $row['total_prizes'];

$auction_schools = array();
$sql = "SELECT u.school_id, s.school_name ";
$sql = $sql . "FROM auction_user_prizes AS aup ";
$sql = $sql . "JOIN users AS u USING (user_id) ";
$sql = $sql . "JOIN schools AS s USING (school_id) ";
$sql = $sql . "WHERE aup.auction_id=" . $auction_id . " ";
if ($school_id > 0)
	$sql = $sql . "AND u.school_id=" . $school_id . " ";
$sql = $sql . "GROUP BY u.school_id";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$auction_school = new auction_school($row);
	array_push($auction_schools, $auction_school);
}

$auction_winners = array();
$sql = "SELECT u.user_id, u.first, u.last, s.school_number, s.school_name, c.class_grade, c.class_sub, pa.prize_id, pa.prize_number, pa.prize_name, pa.prize_points, aw.quantity ";
$sql = $sql . "FROM auction_winners AS aw ";
$sql = $sql . "JOIN users AS u USING (user_id) ";
$sql = $sql . "JOIN schools AS s USING (school_id) ";
$sql = $sql . "JOIN classes AS c USING (class_id) ";
$sql = $sql . "JOIN prizes_auction AS pa USING (prize_id) ";
$sql = $sql . "WHERE aw.auction_id=" . $auction_id . " ";
if ($school_id > 0)
	$sql = $sql . "AND u.school_id=" . $school_id . " ";
$sql = $sql . "ORDER BY pa.prize_points, pa.prize_name ";

$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$auction_winner = new auction_winner($row);
	array_push($auction_winners, $auction_winner);
}

?>

<!--
			<CENTER>
				<B>
					<SPAN STYLE="color:darkblue;">TOTAL TICKETS:</SPAN>&nbsp;<SPAN STYLE="color:blue;"><?//=$total_tickets;?></SPAN>
					<BR />
					<SPAN STYLE="color:darkblue;">TOTAL PRIZES:</SPAN>&nbsp;<SPAN STYLE="color:blue;"><?//=$total_prizes;?></SPAN>
				</B>
			</CENTER>
			
			<BR />
			<BR />			
-->

			<!--<A HREF="admin_auction_winners.php?school_id=<?//=$school_id?>&action=export_winners&amp;auction_id=<?//=$auction_id?>">-->
<!--			<TABLE class="pretty_grid">
				<TR>
					<TH>SCHOOL</TH>
					<th>Registered Users</th>
					<TH>&nbsp;TICKETS&nbsp;</TH>
					<TH>&nbsp;PRIZES&nbsp;</TH>
				</TR>
		
				<? foreach ($auction_schools as $auction_school) : ?>								
				<TR >					
					<TD><?=$auction_school->school_name;?></TD>	
					<td><?=$auction_school->users;?>&nbsp;<span style="color:blue;">(<?=$auction_school->user_percentage;?>%)</span></td>
					<TD>&nbsp;<?=$auction_school->school_tickets;?>&nbsp;<span style="color:blue;">(<?=$auction_school->ticket_percentage;?>%)</span></TD>
					<? if ($auction_school->prizes_won > 0) : ?>
					<TD>&nbsp;<?=$auction_school->prizes_won;?>&nbsp;<span style="color:blue;">(<?=$auction_school->prize_percentage;?>%)</span></TD>				
					<? else : ?>
					<TD>&nbsp;0&nbsp;<span style="color:blue;">(0%)</span></TD>				
					<? endif; ?>
				</TR>
				<? endforeach; ?>				
			</TABLE>
			
			<BR />
			<A name="export_winners" id="export_winners" data="<?=$school_id;?><?=$auction_id;?>">
				Export winners
			</A>			
			<BR />		
-->
			<TABLE class="pretty_grid">
				<TR >
					<TH>NAME</TH>
					<th>&nbsp;USER ID&nbsp;</th>
					<TH>&nbsp;SCHOOL&nbsp;</TH>
					<TH>&nbsp;GRADE&nbsp;</TH>
					<TH>&nbsp;PRIZE&nbsp;</TH>
					<TH>&nbsp;PRIZE ID&nbsp;</TH>
					<TH>&nbsp;QUANTITY #&nbsp;</TH>
				</TR>
		
				<? $prev_prize_id = ""; ?>
				
				<? foreach ($auction_winners as $auction_winner) : ?>
				<? if ($prev_prize_id != "" && $prev_prize_id != $auction_winner->prize_id) { $prize_winner_number = 1; } ?>
				<TR >
					<TD>&nbsp;<?=$auction_winner->first;?>&nbsp;<?=$auction_winner->last;?></TD>
                    <TD>&nbsp;<?=$auction_winner->user_id;?>&nbsp;</TD> 
                    <TD>&nbsp;<?=$auction_winner->school_name;?>&nbsp;</TD> 
					<TD>&nbsp;<?=$auction_winner->class_name;?>&nbsp;</TD>
					<TD>&nbsp;<?=$auction_winner->prize_name;?>&nbsp;(<?=$auction_winner->prize_points;?>)</TD>
					<TD>&nbsp;<?=$auction_winner->prize_id;?>&nbsp;</TD>
					<TD>&nbsp;<?=$auction_winner->quantity;?>&nbsp;</TD>
				</TR>
				<? $prev_prize_id = $auction_winner->prize_id; ?>
				<? endforeach; ?>							
			</TABLE>				
