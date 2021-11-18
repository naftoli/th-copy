<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$user_id = mysql_real_escape_string($_GET['id']);
$testNum = $_GET['test'];

$dates = [
    [
        'start' => 2459469,
        'end'   => 2459514
    ],
    [
        'start' => 2459514,
        'end'   => 2459542
    ],
    [
        'start' => 2459543,
        'end'   => 2459585,
    ],
    [
        'start' => 2459586,
        'end'   => 2459621
    ]
];

$totals = [];
for ($i = 0; $i < $testNum; $i++) {
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
                    AND dtmm.start_date >= " . $dates[$i]['start'] . "
                    AND dtmm.end_date <= " . $dates[$i]['end'] . "
                    AND user_id = " . $user_id;
    $result = mysql_query($sql);
    $total = mysql_fetch_assoc($result)['total'];
    $totals[$i] = $total;
}

echo json_encode($totals);