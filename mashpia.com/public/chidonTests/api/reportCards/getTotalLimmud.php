<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$sql = "SELECT 
            IFNULL(SUM(done_qty), 0) AS total
        FROM
            date_tasks_marks dtm
                JOIN
            date_tasks dt USING (date_task_id)
                JOIN
            date_tasks_missions dtmm USING (date_tasks_mission_id)
        WHERE
            dt.cat = 'chidon limmud'
                AND dtmm.start_date >= 2459469
                AND dtmm.end_date <= 2459514
                AND user_id = " . $_GET['id'];
$result = mysql_query($sql);
$total = mysql_fetch_assoc($result)['total'];
echo $total;