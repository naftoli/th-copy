<?php
require '../../../db.php';
$user = mysql_real_escape_string($_POST['user']);
$story = mysql_real_escape_string($_POST['story']);
$lesson = mysql_real_escape_string($_POST['lesson']);
$year = mysql_real_escape_string($_POST['year']);

$sql = "insert into stories
        set user_id = " . $user . ", 
        story = \"" . $story . "\",
        lesson = \"" . $lesson . "\",
        year = " . $year;
if (mysql_query($sql)) {
    echo 0;
} else {
    echo 1;
}
?>