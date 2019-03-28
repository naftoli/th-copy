<?
require_once 'db.php';

$auction_id = 44;

$tickets = array();
$sql = "select aup.quantity, p.prize_id, p.prize_name, s.school_id, s.school_name, c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.user_id 
		from auction_user_prizes aup 
		join users u using (user_id) 
		join schools s on s.school_id = u.school_id  
		join classes c on c.class_id = u.class_id 
		join prizes_auction p using (prize_id) 
		where aup.auction_id = $auction_id 
		order by p.prize_name, s.school_name, c.class_grade, c.class_sub, u.last, u.first
";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$prize_id = $row['prize_id'];
	$prize = $row['prize_name'];
	$school_id = $row['school_id'];
	$school = $row['school_name'];
	$class = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	$user_id = $row['user_id'];
	$user = $row['first'] . ' ' . $row['last'];
	$qty = $row['quantity'];
	
	//get number of prizes won this year
	$won = 0;
	$sql2 = "select count(user_id) as total from auction_winners where auction_id >= 42 and user_id = " . $row['user_id'];
	$res2 = mysql_query($sql2);
	if (mysql_num_rows($res2) > 0) {
		$row2 = mysql_fetch_assoc($res2);
		$won = $row2['total'];
	} 
	
	if (!$won) 
		$tickets[$prize][$prize_id][$school][$school_id][$class][][$user][$user_id] = $qty; 
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style>
			td {
				text-align: center;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Prize</th>
				<th>Prize ID</th>
				<th>School</th>
				<th>School ID</th>
				<th>Grade</th>
				<th>Student</th>
				<th>Student ID</th>
				<th>Number of Tickets</th>
			</tr>
			<?
			foreach ($tickets as $prize => $info) {
				foreach ($info as $prize_id => $arr) {
					foreach ($arr as $school => $info) {
						foreach ($info as $school_id => $arr) {
							foreach ($arr as $class => $info) {
								foreach ($info as $users => $arr) {
									foreach ($arr as $user => $info) {
										foreach ($info as $user_id => $qty) {
											echo "<tr><td>" . $prize . "</td><td>" . $prize_id . "</td><td>" . 
												$school . "</td><td>" . $school_id . "</td><td>" . $class . "</td><td>" . 
												$user . "</td><td>" . $user_id . "</td><td>" . $qty . "</td></tr>";
										}
									}
								}
							}
						}
					}
				}
			}
			?>
		</table>
	</body>
</html>
