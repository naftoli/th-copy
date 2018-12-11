<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	<style type="text/css">
		th, td {
			padding: 5px;
			font-size: 12px;
		}
	</style>
	<BODY>
	
	<? 
	include("../db.php");
	
	$sql = "select pa.prize_id, pa.prize_name, pa.prize_points, count(aup.user_id) as total, s.school_id, s.school_name
			from prizes_auction pa
			join auction_user_prizes aup using (prize_id)
			join users u using (user_id)
			join schools s on s.school_id = u.school_id 
			where aup.auction_id = 70
			group by aup.prize_id, s.school_id
			order by prize_name, school_name";
	//echo $sql;
	$result = mysql_query($sql);
	?>
		<table>
			<tr>
				<th>Prize ID</th>
				<th>Prize Name</th>
				<th>Prize Points</th>
				<th>School ID</th>
				<th>School Name</th>
				<th>Total Users entered tickets</th>
			</tr>
			<?
			$prize = 0;
			while ($row = mysql_fetch_array($result)) {
				echo "<tr><td>" . $row['prize_id'] . "</td><td>" . $row['prize_name'] . "</td><td>" . $row['prize_points'] .
					"</td><td>" . $row['school_id']	. "</td><td>" . $row['school_name'] . "</td><td>" . $row['total'] . "</td></tr>";
			}
			?>
		</table>
	</BODY>
</HTML>