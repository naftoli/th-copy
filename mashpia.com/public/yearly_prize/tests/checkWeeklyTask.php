<?php
ini_set('display_errors', 1);
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require '../classes/TotalWeeklyTasks.php';
$t = new TotalWeeklyTasks(62705, 2459159);
$t->start_date = 2459153;
echo $t->total_weeks_with_task(true);
echo $t->week_has_task(2459153, 2459159, true);