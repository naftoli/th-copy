<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$sql = "SELECT 
            IFNULL(SUM(done_qty), 0) as total
        FROM
            date_tasks_marks dtm
                JOIN
            date_tasks dt USING (date_task_id)
        WHERE
            dt.cat = 'chidon limmud'
                AND dtm.user_id = " . $_GET['id'];
$result = mysql_query($sql);
$total = mysql_fetch_assoc($result)['total'];
echo $total;