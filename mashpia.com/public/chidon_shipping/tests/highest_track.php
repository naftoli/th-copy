<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once '../class.chidonShipping.php';
$cs = new ChidonShipping();

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

function showIyun($child) {
    global $ct;
    $ct->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
    $ct->setScores();
    $scores = $ct->getScores();
    $cumulative_track = $ct->calculateCumulative($child, $scores);
    return $cumulative_track;
//    return $child['highest_track'] == 'iyun' || $cumulative_track == 'iyun';
}

$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        where tc.year = $year 
        and tc.user_id = " . $_GET['user'];
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

//echo $cs->getTrackByTests($row);

echo showIyun($row);