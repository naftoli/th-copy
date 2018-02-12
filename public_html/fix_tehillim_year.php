<?
require_once 'db.php';

$users = array();
$sql = "select * from users";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];   
}

foreach ($users as $id) {
    $sql = "select * from user_tracks where user_id = $id and subject_id = 41";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $level = $row['level'];
        mysql_query("update user_tracks set level = $level where subject_id = 1 and user_id = $id");
    }
}
