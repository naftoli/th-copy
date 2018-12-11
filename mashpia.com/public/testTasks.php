<?php
$jd = unixtojd();
$day = date('w');
$start = $jd - $day - 2;
$end = $start + 6;

require_once 'db.php';
require_once 'class.defaults.php';
require_once 'class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
$tc->setStart( $start );
$tc->setEnd( $end );
$tc->setType( 0, 0, 49 );
$tasks = $tc->getTasks( 1, true );
echo "<pre>"; print_r($tasks); echo "</pre>";