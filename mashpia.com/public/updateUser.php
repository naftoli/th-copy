<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<?php
require_once('db.php');
require_once('classes/medal_updater.php');
require_once('classes/rank_updater.php');

$mupdater = new medal_updater();
$rupdater = new rank_updater();

//$users = array();
//$sql = "select * from date_tasks_marks dtm
//        join date_tasks dt using (date_task_id) 
//        where dt.grid_id = 8001
//        and dtm.mark_date = 2458076";
//$result = mysql_query( $sql );
//while ($row = mysql_fetch_assoc( $result )) {
//    $users[] = $row['user_id'];
//}

$users = array(17572);
foreach ($users as $user) {
    $mupdater->update_medal_two($user);
    $rupdater->update_rank_two($user);
}
echo "Done.";
?>
</html>