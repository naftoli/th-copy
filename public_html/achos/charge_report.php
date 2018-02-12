<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Charge It Missions Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1>Charge It Missions Report</h1>
        
        <?
        $sql = "SELECT dt.date_task_id, dt.name, dtm.start_date, dtm.end_date
				FROM date_tasks dt
				JOIN date_tasks_missions dtm
				USING ( date_tasks_mission_id ) 
				WHERE dtm.start_date > 2456382
				AND dt.focus_task =1";
		$result = mysql_query( $sql );
		?>
		<table>
			<tr>
				<th>Task ID</th>
				<th>Name</th>
				<th>Start Date</th>
				<th>End Date</th>
			</tr>
			<?
			while ( $row = mysql_fetch_assoc($result) ) {
				echo "<tr><td>" . $row['date_task_id'] . "</td><td>" . 
					$row['name'] . "</td><td>" . 
					jdtogregorian( $row['start_date'] ) . "</td><td>" .  
					jdtogregorian( $row['end_date'] ) . "</td></tr>";
			}
	        ?>
        </table>
	</body>
</html>