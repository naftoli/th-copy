<?
require_once '../class.db.php';
$pdo = DB::getInstance();

//calculates total count for nested array
function totalCount( $arr, &$total = 0 ) {
    if ( is_array( $arr ) ) {
        foreach ( $arr as $k => $val ) {
            if ( !is_array( $val ) ) {
                $total += count( $arr );
                break;
            } else {
                totalCount( $val, $total );
            }
        }
    } 
    return $total;
}

//caclulates all shabbos mevorchim dates for given year
function calculateSM( $year ) {
    $sm = array(); 
    $day = 30;
    //first get last sm for previous year
    $date = jewishtojd( 13, $day, ($year-1) );
    $date += 1; //fix issue with jdtounix showing a day off
    $time = jdtounix( $date );
    $dayOfWeek = date( "w", $time );
    $shabbosMevorchim = $date - ($dayOfWeek + 2); //really should be adding 1 but because of jdtounix fix need to add 2
    $sm[0] = $shabbosMevorchim;
    for ( $i = 1; $i < 13; $i++ ) {
        $date = jewishtojd( $i, $day, $year );
        $date += 1; //fix issue with jdtounix showing a day off
        $time = jdtounix( $date );
        $dayOfWeek = date( "w", $time );
        $shabbosMevorchim = $date - ($dayOfWeek + 2); //really should be adding 1 but because of jdtounix fix need to add 2
        $sm[$i] = $shabbosMevorchim; //note: if value of index #6 == index #7 then that means that it is NOT a leap year
    }
    return $sm;
}
echo "<pre>";
$year = isset( $_GET['year'] ) ? $_GET['year'] : 5775;
$sm = calculateSM( $year );
//print_r($sm);

//prepare mission and task queries
$missionSql = "select date_tasks_mission_id, mission_description, speed, school_type_id    
               from date_tasks_missions 
               where start_date = :start 
               and end_date = :end 
               and level = :level 
               and track_id = $ladder 
               and subject_id = 1 
               order by school_type_id";
$missionStmt = $pdo->prepare( $missionSql );

$taskSql = "select date_task_id, name, description from date_tasks 
            where date_tasks_mission_id = :id";
$taskStmt = $pdo->prepare( $taskSql );

$levels = array();
for ( $i = 6; $i < 15; $i++ ) {
    $levels[] = $i;
}

$info = array();
foreach ( $levels as $level ) {
    foreach ( $sm as $date ) {
        $jewish = iconv( 'WINDOWS-1255', 'UTF-8', jdtojewish( $date, true, CAL_JEWISH_ADD_ALAFIM_GERESH + CAL_JEWISH_ADD_GERESHAYIM ) ); 
        $params = array( 
                ':start' => $date, 
                ':end'   => $date, 
                ':level' => $level, 
            );
        $missionStmt->execute( $params );
        if ( $missionStmt->rowCount() > 0 ) {
            while ( $row = $missionStmt->fetch( PDO::FETCH_ASSOC ) ) {
	            $id = $row['date_tasks_mission_id'];
	            $name = $row['mission_description'] . ":" . $row['speed'];
	            $school_type = $row['school_type_id'];
                $taskParams = array(':id'   => $id);
                $taskStmt->execute( $taskParams );
                if ( $taskStmt->rowCount() > 0 ) {
                    while ( $row2 = $taskStmt->fetch( PDO::FETCH_ASSOC ) ) {
	                    //$taskID = $row2['date_task_id'];
	                    $info[$level][$date][$id][$school_type][$name][] = $row2['description'];
					}
                } else {
                    $info[$level][$date][$id][$school_type][$name] = 'task info missing';
                }
            }
        } else {
            $info[$level][$date][][$jewish][] = 'mission missing';
        }
    }
}

//print_r( $info );
echo "</pre>";


function generateReport() {
    global $year, $ladder, $info; 
    ?>
    <h2><?=$year . " Ladder " . $ladder?></h2>
    <table>
        <tr>
            <th>School Type</th>
            <th>Year</th>
            <th>Julian Date</th>
            <th>Mission ID</th>
            <th>Mission</th>
            <th>Kapitelach</th>
            <th>Minutes</th>
            <th>Speed</th>
        </tr>
        <? 
        foreach ( $info as $level => $dates ) {
            foreach ( $dates as $date => $ids ) {
                foreach ( $ids as $id => $school_types ) {
                	foreach ( $school_types as $school_type => $missions ) { 
	                    foreach ( $missions as $mission => $tasks ) {
	                    	$missionInfo = explode( ':', $mission );
							echo "<tr><td>" . $school_type . "</td>";
	                        echo "<td>" . $level . "</td>";
	                        echo "<td>" . $date . "</td>";
	                        echo "<td>" . $id . "</td>";
	                        echo "<td>" . $missionInfo[0] . "</td>";
	                        if ( isset( $tasks['Kapitelach'] ) ) 
	                            echo "<td>" . $tasks['Kapitelach'] . "</td>";
	                        else 
	                            echo "<td>" . $tasks[0] . "</td>";
	                        if ( isset( $tasks['Minutes'] ) )
	                            echo "<td>" . $tasks['Minutes'] . "</td>";
	                        else 
	                            echo "<td>" . $tasks[0] . "</td>";
	                        echo "<td>" . $missionInfo[1] . "</td></tr>";
	                    }
					}
                }
            }
        }
        ?>
    </table>
    <?
}
?>                   