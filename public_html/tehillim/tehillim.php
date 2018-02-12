<?
$admin_auth = array('school'); 
require('../header.php');
?>
<html>
    <head>
        <meta charset="UTF-8">
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    
    <body>
    	<?
    	$ladders = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 );
		foreach ( $ladders as $ladder ) {
	    	//load spreadsheet
		    if ( ( $handle = fopen( "TehillimLadder{$ladder}.csv", "r" ) ) !== false ) {
		    	$row = 1; 
				$keys = array();
		    	while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
					if ( $row++ == 1 ) {
						//header row so skip
						continue;
					} else {
						//put column values into variables
						$i = 0;
						$year = $data[$i++];
						$date = $data[$i++];
						$i++;
						$description = $data[$i++];
						$kapitelach = $data[$i++];
						$amount = $data[$i++];
						$minutes = $data[$i++];
						$speed = $data[$i++];
												
						//find out which school_types are in the system for the current year/ladder/date
						$found = array();
						$qry = "select date_tasks_mission_id, school_type_id from date_tasks_missions 
								where subject_id = 1  
								and level = $year 
								and track_id = $ladder 
								and start_date = $date 
								and end_date = $date";
						$res = mysql_query( $qry );
						while ( $r = mysql_fetch_assoc( $res ) ) {
							$found[$r['school_type_id']] = $r['date_tasks_mission_id'];
						}
						
						//compare found types to needed types
						$needed = array( 2, 3, 12, 13 );
						foreach( $needed as $v ) {
							if ( array_key_exists( $v, $found ) ) {
								//update mission / tasks
								$sql = "update date_tasks_missions    
										set mission_description = '" . $description . "', 
										speed = " . $speed . " 
										where date_tasks_mission_id = " . $found[$v];
								if ( $found[$v] == 11262 ) {
									echo $sql . "<br />";
									break;
								}
								mysql_query( $sql );
								
								//make sure all tasks exist and update or create if needed
								$tasksNeeded = array(
									'How many Kapitlach did you say?', 
									'How many minutes did you spend saying תהלים?', 
									'I said it in שול or in a group.'
								);
								$tasksFound = array();
								
								$sql2 = "select * from date_tasks where date_tasks_mission_id = " . $found[$v];
								$result2 = mysql_query( $sql2 );
								while ( $row2 = mysql_fetch_assoc( $result2 ) ) {
									if ( $row2['name'] == 'How many Kapitlach did you say?' ) {
										$tasksFound[] = 0; 
										$sql3 = "update date_tasks 
												 set description = '" . $kapitelach . "', 
												 quantity = " . $amount . " 
												 where date_task_id = " . $row2['date_task_id'];
									} else if ( $row2['name'] == 'How many minutes did you spend saying תהלים?' ) {
										$tasksFound[] = 1; 
										$sql3 = "update date_tasks 
												 set description = '" . $minutes . " min', 
												 quantity = $minutes, 
												 where date_task_id = " . $row2['date_task_id'];
									} else if ( $row2['name'] == 'I said it in שול or in a group.' ) {
										$tasksFound[] = 2;
									}
									mysql_query( $sql3 );
								}
								
								//create missing tasks
								for ( $j = 0; $j < count( $tasksNeeded ); $j++ ) {
									if ( !in_array( $j, $tasksFound ) ) {
										switch( $j ) {
											case 0:
												$task = "insert into date_tasks 
														set date_tasks_mission_id = " . $found[$v] . ", 
														ord = 0, 
														name = 'How many Kapitlach did you say?', 
														description = '$kapitelach', 
														mandatory_qty = 1, 
														optional_qty = 0, 
														is_bonus = 0, 
														label_id = 37, 
														quantity = $amount, 
														points = 5.0, 
														daily_task = 1";
												break;
											case 1:
												$task = "insert into date_tasks 
														set date_tasks_mission_id = " . $found[$v] . ", 
														ord = 1, 
														name = 'How many minutes did you spend saying תהלים?', 
														description = '$minutes min', 
														mandatory_qty = 1, 
														optional_qty = 0, 
														is_bonus = 0, 
														label_id = 37, 
														quantity = $minutes, 
														points = 2.0, 
														daily_task = 1";
												break;
											case 2:
												$task = "insert into date_tasks 
														set date_tasks_mission_id = " . $found[$v] . ", 
														ord = 2, 
														name = 'I said it in שול or in a group.', 
														description = 'Say it in Shul.', 
														mandatory_qty = 0, 
														optional_qty = 1, 
														is_bonus = 0, 
														label_id = 37, 
														points = 1.0, 
														daily_task = 1";
												break;
										}
										mysql_query( $task );
									}
								}
							} else {
								//create mission / tasks
								//get mission name
								$mission = explode( ' ', $description );
								$mission_name = $mission[2] . (count( $mission ) == 4 ? ' ' . $mission[3] : '');
								//create mission
								$missionSql = "insert into date_tasks_missions 
												set school_type_id = $v, 
												subject_id = 1, 
												level = $year, 
												track_id = $ladder,  
												mission_name = '$mission_name',  
												mission_description = '$description',  
												mission_value = 1.0, 
												start_date = $date, 
												end_date = $date, 
												speed = $speed";
								mysql_query( $missionSql );
								$mission_id = mysql_insert_id();
								
								//create kapitelach task
								$task1 = "insert into date_tasks 
											set date_tasks_mission_id = $mission_id, 
											ord = 0, 
											name = 'How many Kapitlach did you say?', 
											description = '$kapitelach', 
											mandatory_qty = 1, 
											optional_qty = 0, 
											is_bonus = 0, 
											label_id = 37, 
											quantity = $amount, 
											points = 5.0, 
											daily_task = 1";
								mysql_query( $task1 );
								
								//create minutes task
								$task2 = "insert into date_tasks 
											set date_tasks_mission_id = $mission_id, 
											ord = 1, 
											name = 'How many minutes did you spend saying תהלים?', 
											description = '$minutes min', 
											mandatory_qty = 1, 
											optional_qty = 0, 
											is_bonus = 0, 
											label_id = 37, 
											quantity = $minutes, 
											points = 2.0, 
											daily_task = 1";
								mysql_query( $task2 );
											
								//create optional task
								$task3 = "insert into date_tasks 
											set date_tasks_mission_id = $mission_id, 
											ord = 2, 
											name = 'I said it in שול or in a group.', 
											description = 'Say it in Shul.', 
											mandatory_qty = 0, 
											optional_qty = 1, 
											is_bonus = 0, 
											label_id = 37, 
											points = 1.0, 
											daily_task = 1";
								mysql_query( $task3 );
							}
						}
					}
				}
		    }
		}
		echo "Done";
    	?>
    </body>
</html>        