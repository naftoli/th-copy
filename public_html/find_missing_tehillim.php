<?
$admin_auth = array('school');
require_once 'header.php';
?>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<pre>
<?
			$sm = calculateSM(5774);
			$school_types = array(12,13);
			$years = array();
			for ($i = 6; $i < 15; $i++) {
				$years[] = $i;
			}
			$tracks = array();
			for ($i = 3; $i < 8; $i++) {
				$tracks[] = $i;
			}
			
			$dtm = array();
			$sql = "show columns from date_tasks_missions";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$dtm[] = $row['Field'];
			}
			
			$dt = array();
			$sql = "show columns from date_tasks";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$dt[] = $row['Field'];
			}
			
			$missing = array();
			foreach ($sm as $date) {
				foreach ($tracks as $track) {
					foreach ($years as $year) {
						foreach ($school_types as $type) {
							$sql = "select * from date_tasks_missions 
									where subject_id = 1 
									and school_type_id = $type 
									and level = $year 
									and track_id = $track 
									and start_date = $date 
									and end_date = $date";
							$result = mysql_query($sql);
							if (mysql_num_rows($result) == 0) {
								 $missing[$date][$track][$year][$type] = true;
							}
						}
					}
				}
			}
			
			//array that stores all sm info
			$info = array(
				6 => array(
					array(
						3 => array(
							'minutes' => 10, 
							'kapitelach' => 'א' 
						)
					)
				), 
				7 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				), 
				8 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				), 
				9 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				), 
				10 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				), 
				11 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				), 
				12 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				), 
				13 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				), 
				14 => array(
					array(
						3 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						4 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						5 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						6 => array(
							'minutes' => , 
							'kapitelach' => '' 
						), 
						7 => array(
							'minutes' => , 
							'kapitelach' => '' 
						)
					)
				)
			);
			print_r($info);
			/*
			foreach ($sm as $date) {
				foreach ($tracks as $track) {
					foreach ($years as $year) {
						foreach ($school_types as $type) {
							//if ($track == 1 && $year == 6 && $type == 2 )
						}
					}
				}
			}
			 * 
			 */
			?>
		</pre>
	</body>
</html>