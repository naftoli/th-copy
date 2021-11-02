<?php
header("Access-Control-Allow-Origin: *");
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$total = 0;
$sql = "SELECT 
            SUM(done_qty) as total
        FROM
            date_tasks_marks dtm
                JOIN
            date_tasks dt USING (date_task_id)
        WHERE
            dt.cat = 'chidon limmud'
                AND dtm.user_id = " . $_GET['id'];
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
    $total = mysql_fetch_assoc($result)['total'];
}
echo $total;