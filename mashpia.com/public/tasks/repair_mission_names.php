<?php
require_once '../db.php';

$info = [];
$sql = "
SELECT 
	dtm.date_tasks_mission_id, 
    p.name AS parsha
FROM
    date_tasks_missions dtm
        JOIN
    parshos p ON (p.start <= dtm.start_date
        && p.end >= dtm.end_date)
WHERE
    dtm.start_date >= 2458362 
    and dtm.personal = 0 
    and subject_id not in (1,40) 
    and name != mission_name";

$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}

echo "<meta charset='utf8' />";
foreach ( $info as $row ) {
    $sql = "update date_tasks_missions set mission_name = '" . $row['parsha'] . "' where date_tasks_mission_id = " . $row['date_tasks_mission_id'];
    //echo $sql . "<br />";
    mysql_query( $sql );
}
echo "done.";