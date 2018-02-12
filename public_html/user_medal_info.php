<?
require 'db.php';

$sql = "select m.medal_name, s.subject_name, u.last, u.first, mm.date_awarded 
from medal_marks mm 
join subjects s using (subject_id) 
join users u using (user_id) 
join medals m using (medal_ord) 
where user_id = 4197 
order by s.subject_name, m.medal_ord";

$result = mysql_query($sql);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style>
			th, td {
				border: 1px solid black;
				padding: 5px;
			}
		</style>		
	</head>
	<body>
		<table>
			<tr>
				<th>Subject</th>
				<th>Medal</th>
				<th>Date Awarded</th>
			</tr>
			<?
				while ($row = mysql_fetch_assoc($result)) {
					echo "<tr>";
					echo "<td>" . $row['subject_name'] . "</td>";
					echo "<td>" . $row['medal_name'] . "</td>";
					echo "<td>" . jdtogregorian($row['date_awarded']) . "</td>";
					echo "</tr>";
				}
			?>
		</table>
	</body>
</html>
