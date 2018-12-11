<?php
require 'db.php';

$info = array();
$sql = "select user_id, lines_learned from lines_learned where campaign_id = 11 
        and user_id in (
        select user_id from users where school_id = 19 
        and class_id in (select class_id from classes where school_id = 19 and class_era = 0 order by class_grade, class_sub)
        and (user_registered > 0 or yan = 1) group by user_id)";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[$row['user_id']] = $row['lines_learned'];
}
//echo "<pre>"; print_r($info); echo "</pre>";

$num = 0;
foreach ($info as $lines) {
    $num += $lines;
}
echo "Total 1: " . $num . "<br />";

$sql = "select sum(lines_learned) as total from lines_learned where campaign_id = 11 
        and user_id in (
        select user_id from users where school_id = 19 
        and class_id in (select class_id from classes where school_id = 19 and class_era = 0 order by class_grade, class_sub)
        and (user_registered > 0 or yan = 1))";
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
echo "Total 2: " . $row['total'] . "<br />";

// get rid of duplicate marks
$duplicates = array();
$sql = "select * from lines_learned where campaign_id in (11,12)";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $duplicates[$row['campaign_id']][$row['user_id']][] = $row;
}

//echo "<pre>"; print_r($duplicates); echo "</pre>"; exit;

foreach ($duplicates as $campaign => $other) {
    foreach ($other as $user => $info) {
        if (count($info) > 1) {
            // get info
            rsort($info);
            echo "<pre>"; print_r($info); echo "</pre>";
            $learned = $info[0]['lines_learned'];
            $sql = "delete from lines_learned
                    where campaign_id = " . $campaign . "
                    and user_id = " . $user;
            echo $sql . "<br />";
            mysql_query( $sql );
            $school_id = $info[0]['school_id'];
            $class_id = $info[0]['class_id'];
            $mission_amount = $info[0]['mission_sheet_amount'];
            $sql = "insert into lines_learned
                    set campaign_id = " . $campaign . ",
                    user_id = " . $user . ",
                    lines_learned = " . $learned . ",
                    school_id = " . $school_id . ",
                    class_id = " . $class_id;
            if ($mission_amount) $sql .= ", mission_sheet_amount = " . $mission_amount;
            echo $sql . "<br />";
            mysql_query($sql);
        }
    }
}
echo "done";