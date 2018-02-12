<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once("../class.totalWeeklyTasks.php");

$subject = new TotalWeeklyTasks(51359, 2458046);
$subject->start_date = 2458019; // update the start date for the tests
$subject->get_week_dates();
?>

<h1>Object Created with new TotalWeeklyTasks(51359, 2458046);</h1>
<pre>
    <?print_r($subject) ?>
</pre>

<pre>
    $subject->total_weeks_with_task() == <?print_r($subject->total_weeks_with_task()) ?>
</pre>

<pre>
    $subject->week_has_task(2458019, 2458025) == <?print_r($subject->week_has_task(2458019, 2458025) ? "true" : "false") ?>
</pre><pre>
    $subject->week_has_task(2458026, 2458032) == <?print_r($subject->week_has_task(2458026, 2458032) ? "true" : "false") ?>
</pre><pre>
    $subject->week_has_task(2458033, 2458039) == <?print_r($subject->week_has_task(2458033, 2458039) ? "true" : "false") ?>
</pre><pre>
    $subject->week_has_task(2458040, 2458046) == <?print_r($subject->week_has_task(2458040, 2458046) ? "true" : "false") ?>
</pre><!--<pre>
    $subject->week_has_task(2458047, 2458053) == <?print_r($subject->week_has_task(2458047, 2458053) ? "true" : "false") ?>
</pre>-->