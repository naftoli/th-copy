<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			#main {
				margin: auto;
				width: 600px;
			}
			table {
				float: left;
				width: 300px;
				padding: 20px;
			}
			tr, th, td {
				padding: 5px;
				font-size: 14px;
				font-family: "sans-script";
				border: 1px solid black;
			}
			th {
				background-color: silver;
			}
			tr:nth-child(odd) {
				background-color: #e8e8e8;
			}
		</style>
	</head>
	
	<body>
		<div id="main">
			<?
			require 'db.php';
			
			$subjects = array();
			$sql = "select subject_id, subject_name from subjects";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$subjects[$row['subject_id']] = $row['subject_name'];
			}
			
			$medals = array();
			$sql = "select medal_ord, medal_name from medals";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$medals[$row['medal_ord']] = $row['medal_name'];
			}
			
			$ranks = array();
			$sql = "select rank_ord, rank_name from ranks";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$ranks[$row['rank_ord']] = $row['rank_name'];
			}
			
			$dates = array(
				2456893	=>	'August 23, 2014', 
				2457096	=>	'March 14, 2015'
			);
			
			foreach ($dates as $date => $format) {
				$grandTotal = 0;
				echo "<table><caption>Ranks Earned from " . $format . "</caption>
					<tr><th>Rank</th><th>Total</th></tr>";
				$sql = "select rank_ord, count(*) as total from rank_marks 
						where date_promoted > $date  
						group by rank_ord";
				$result = mysql_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$grandTotal += $row['total'];
					echo "<tr><td>" . $ranks[$row['rank_ord']] . "</td><td>" . 
						$row['total'] . "</td></tr>";
				}
				echo "<tr><th>Total</th><th>" . $grandTotal . "</th></tr>";
				echo "</table>";
			}
			echo "<div style='clear:both'></div>";
			
			$grandTotals = array();
			foreach ($dates as $date => $format) {
				$gTotal = 0;
				echo "<table><caption>Medals Earned from " . $format . "</caption><tr><th>Subject</th>
						<th>Rank</th><th>Total</th></tr>";
				$sql = "select subject_id, medal_ord, count(*) as total from medal_marks 
						where date_awarded > $date  
						group by subject_id, medal_ord";
				$result = mysql_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$gTotal += $row['total'];
					if (isset($grandTotals[$date][$row['subject_id']])) {
						$grandTotals[$date][$row['subject_id']] += $row['total'];
					} else {
						$grandTotals[$date][$row['subject_id']] = $row['total'];
					}
					echo "<tr><td>" . $subjects[$row['subject_id']] . "</td><td>" . 
						$medals[$row['medal_ord']] . "</td><td>" . $row['total'] . "</td></tr>";
				}
				echo "<tr><th></th><th>Total</th><th>" . $gTotal . "</th></tr>";
				echo "</table>"; 
			}
			echo "<div style='clear:both'></div>";
			
			foreach ($dates as $date => $format) {
				$gTotal = 0;
				echo "<table><caption>Grand Totals for Medals Earned from " . $format . "</caption>";
				echo "<tr><th>Subject</th><th>Grand Total</th></tr>";
				foreach ($grandTotals[$date] as $subject => $total) {
					$gTotal += $total;
					echo "<tr><td>" . $subjects[$subject] . "</td><td>" . $total . "</td></tr>";
				}
				echo "<tr><th>Total</th><th>" . $gTotal . "</th></tr>";
				echo "</table>";
			}
			?>
		</div>
	</body>
</html>