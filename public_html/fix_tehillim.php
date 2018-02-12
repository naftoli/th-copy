<?php
require_once 'db.php';
$info = array();
$sql = "select * from medal_marks_bk";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
echo "<pre>";
//print_r($info);
echo "</pre>";
foreach ($info as $row) {
    $sql = "select * from medal_marks
            where user_id = " . $row['user_id'] . "
            and medal_ord = " . $row['medal_ord'] . "
            and subject_id = " . $row['subject_id'];
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $sql = "update medal_marks
                set date_awarded = " . $row['date_awarded'] . ",
                date_shipped = '" . $row['date_shipped'] . "',
                date_received = '" . $row['date_received'] . "',
                medals_updated = " . $row['medals_updated'] . ",
                prof_medals_updater = " . $row['prof_medals_updater'] . ",
                new_system_updated = " . $row['new_system_updated'] . "
                where medal_ord = " . $row['medal_ord'] . ",
                subject_id = " . $row['subject_id'] . ",
                user_id = " . $row['user_id'];
        //echo $sql . "<br />";
        mysql_query($sql);
    }
}
echo "done";