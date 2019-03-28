<?php 
ini_set('display_errors', 1);

$link = mysql_connect('localhost', 'mashpia', 'ShJ1uWcT89Ek6E') or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
mysql_query('SET NAMES utf8');
mysql_query('SET CHARACTER_SET utf8');
mysql_select_db('mashpiadb') or trigger_error_server('Failed to select db', E_USER_ERROR);

$num = $_POST['iteration'] ? $_POST['iteration'] : 0;
$num *= 10000;
$info = array();
$sql = "select * from date_tasks_marks dtm 
        join date_tasks dt using (date_task_id) 
        where dtm.done_qty = 3 
        and dtm.date_task_id >= 4110112 
        and dt.quantity is not null 
        limit $num, 10000";

//$sql = "select * from date_tasks_marks where done_qty = 3 and date_task_id >= 4825112";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>";

$qrys = array();
mysql_select_db('mashpiadb_sf01220566') or trigger_error_server('Failed to select db', E_USER_ERROR);;
foreach ($info as $row) {
    $sql2 = "select done_qty from date_tasks_marks where user_id = " . $row['user_id'] . " and date_task_id = " . $row['date_task_id'] . " and mark_date = " . $row['mark_date'];
    $result2 = mysql_query($sql2);
    if ($row2 = mysql_fetch_assoc($result2)) {
        if ($row2['done_qty'] > 0 && $row['done_qty'] != $row2['done_qty']) {
            $sql3 = "update mashpiadb.date_tasks_marks set done_qty = " . $row2['done_qty'] . " where date_task_id = " . $row['date_task_id'] . " and user_id = " . $row['user_id'] . "
                    and mark_date = " . $row['mark_date'];
            //echo $sql3 . "<br />";
            $qrys[] = $sql3;
        }
    }
}

//echo "<pre>"; print_r($qrys); echo "</pre>";
foreach ($qrys as $qry) {
    if (!mysql_query($qry)) {
        echo $qry . "<br />" . mysql_error() . "<br />";
    }
}
echo "Done."; 